<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller;

use Eccube\Entity\Customer;
use Eccube\Entity\CustomerAddress;
use Eccube\Entity\Order;
use Eccube\Entity\Shipping;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\ShoppingException;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Form\Type\Front\ShoppingShippingType;
use Eccube\Form\Type\Shopping\CustomerAddressType;
use Eccube\Form\Type\Shopping\OrderType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\TradeLawRepository;
use Eccube\Service\CartService;
use Eccube\Service\MailService;
use Eccube\Service\OrderHelper;
use Eccube\Service\Payment\PaymentDispatcher;
use Eccube\Service\Payment\PaymentMethodInterface;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Psr\Container\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Controller\ShoppingController as BaseShoppingController;
use Customize\Service\SubscriptionProcess;



class ShoppingController extends BaseShoppingController
{
    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ContainerInterface
     */
    protected $serviceContainer;

    /**
     * @var BaseInfoRepository
     */
    protected $baseInfoRepository;

    /**
     * @var TradeLawRepository
     */
    protected TradeLawRepository $tradeLawRepository;

    protected RateLimiterFactory $shoppingConfirmIpLimiter;

    protected RateLimiterFactory $shoppingConfirmCustomerLimiter;

    protected RateLimiterFactory $shoppingCheckoutIpLimiter;

    protected RateLimiterFactory $shoppingCheckoutCustomerLimiter;

    /**
     * @var SubscriptionProcess
     */
    protected $subscriptionProcess;

    public function __construct(
        CartService $cartService,
        MailService $mailService,
        OrderRepository $orderRepository,
        OrderHelper $orderHelper,
        ContainerInterface $serviceContainer,
        TradeLawRepository $tradeLawRepository,
        RateLimiterFactory $shoppingConfirmIpLimiter,
        RateLimiterFactory $shoppingConfirmCustomerLimiter,
        RateLimiterFactory $shoppingCheckoutIpLimiter,
        RateLimiterFactory $shoppingCheckoutCustomerLimiter,
        BaseInfoRepository $baseInfoRepository,
        SubscriptionProcess $subscriptionProcess,
        iterable $paymentMethods
    ) {
        $this->cartService = $cartService;
        $this->mailService = $mailService;
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
        $this->serviceContainer = $serviceContainer;
        $this->tradeLawRepository = $tradeLawRepository;
        $this->shoppingConfirmIpLimiter = $shoppingConfirmIpLimiter;
        $this->shoppingConfirmCustomerLimiter = $shoppingConfirmCustomerLimiter;
        $this->shoppingCheckoutIpLimiter = $shoppingCheckoutIpLimiter;
        $this->shoppingCheckoutCustomerLimiter = $shoppingCheckoutCustomerLimiter;
        $this->baseInfoRepository = $baseInfoRepository;
        $this->subscriptionProcess = $subscriptionProcess;
        $this->paymentMethods = [];
        foreach ($paymentMethods as $service) {
            $this->paymentMethods[get_class($service)] = $service;
        }
    }

    /**
     * 注文処理を行う.
     *
     * 決済プラグインによる決済処理および注文の確定処理を行います.
     *
     * @Route("/shopping/checkout", name="shopping_checkout", methods={"POST"})
     * @Template("Shopping/confirm.twig")
     */
    public function checkout(Request $request) {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文処理] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            log_info('[注文処理] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        // フォームの生成.
        $form = $this->createForm(OrderType::class, $Order, [
            // 確認画面から注文処理へ遷移する場合は, Orderエンティティで値を引き回すためフォーム項目の定義をスキップする.
            'skip_add_form' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('[注文処理] 注文処理を開始します.', [$Order->getId()]);

            try {
                /*
                 * 集計処理
                 */
                log_info('[注文処理] 集計処理を開始します.', [$Order->getId()]);
                $response = $this->executePurchaseFlow($Order);
                $this->entityManager->flush();

                if ($response) {
                    return $response;
                }

                log_info('[注文処理] PaymentMethodを取得します.', [$Order->getPayment()->getMethodClass()]);
                $paymentMethod = $this->createPaymentMethod($Order, $form);

                /*
                 * 決済実行(前処理)
                 */
                log_info('[注文処理] PaymentMethod::applyを実行します.');
                if ($response = $this->executeApply($paymentMethod)) {
                    return $response;
                }

                /*
                 * 決済実行
                 *
                 * PaymentMethod::checkoutでは決済処理が行われ, 正常に処理出来た場合はPurchaseFlow::commitがコールされます.
                 */
                log_info('[注文処理] PaymentMethod::checkoutを実行します.');
                if ($response = $this->executeCheckout($paymentMethod)) {
                    return $response;
                }

                $this->entityManager->flush();

                log_info('[注文処理] 注文処理が完了しました.', [$Order->getId()]);
            } catch (ShoppingException $e) {
                log_error('[注文処理] 購入エラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();

                $this->addError($e->getMessage());

                return $this->redirectToRoute('shopping_error');
            } catch (\Exception $e) {
                log_error('[注文処理] 予期しないエラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();

                $this->addError('front.shopping.system_error');

                return $this->redirectToRoute('shopping_error');
            }

            $Customer = $this->getUser();
            $Cart = $this->cartService->getCart();
            $CartItems = $Cart->getCartItems();
            $this->subscriptionProcess->order($Customer, $CartItems, $Order);

            // カート削除
            log_info('[注文処理] カートをクリアします.', [$Order->getId()]);
            $this->cartService->clear();

            // 受注IDをセッションにセット
            $this->session->set(OrderHelper::SESSION_ORDER_ID, $Order->getId());

            // メール送信
            log_info('[注文処理] 注文メールの送信を行います.', [$Order->getId()]);
            $this->mailService->sendOrderMail($Order);
            // $this->entityManager->flush();

            log_info('[注文処理] 注文処理が完了しました. 購入完了画面へ遷移します.', [$Order->getId()]);

            return $this->redirectToRoute('shopping_complete');
        }

        log_info('[注文処理] フォームエラーのため, 購入エラー画面へ遷移します.', [$Order->getId()]);

        return $this->redirectToRoute('shopping_error');
    }

    /**
     * PaymentMethodをコンテナから取得する.
     *
     * @param Order $Order
     * @param FormInterface $form
     *
     * @return PaymentMethodInterface
     */
    private function createPaymentMethod(Order $Order, FormInterface $form)
    {
        $class = $Order->getPayment()->getMethodClass();

        if (!isset($this->paymentMethods[$class])) {
            throw new \RuntimeException("$class is not injected in ShoppingController.");
        }

        $PaymentMethod = $this->paymentMethods[$class];
        $PaymentMethod->setOrder($Order);
        $PaymentMethod->setFormType($form);

        return $PaymentMethod;
    }

    /**
     * PaymentMethod::applyを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function executeApply(PaymentMethodInterface $paymentMethod)
    {
        $dispatcher = $paymentMethod->apply(); // 決済処理中.

        // リンク式決済のように他のサイトへ遷移する場合などは, dispatcherに処理を移譲する.
        if ($dispatcher instanceof PaymentDispatcher) {
            $response = $dispatcher->getResponse();
            $this->entityManager->flush();

            // dispatcherがresponseを保持している場合はresponseを返す
            if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                log_info('[注文処理] PaymentMethod::applyが指定したレスポンスを表示します.');

                return $response;
            }

            // forwardすることも可能.
            if ($dispatcher->isForward()) {
                log_info('[注文処理] PaymentMethod::applyによりForwardします.',
                    [$dispatcher->getRoute(), $dispatcher->getPathParameters(), $dispatcher->getQueryParameters()]);

                return $this->forwardToRoute($dispatcher->getRoute(), $dispatcher->getPathParameters(),
                    $dispatcher->getQueryParameters());
            } else {
                log_info('[注文処理] PaymentMethod::applyによりリダイレクトします.',
                    [$dispatcher->getRoute(), $dispatcher->getPathParameters(), $dispatcher->getQueryParameters()]);

                return $this->redirectToRoute($dispatcher->getRoute(),
                    array_merge($dispatcher->getPathParameters(), $dispatcher->getQueryParameters()));
            }
        }
    }

    /**
     * PaymentMethod::checkoutを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function executeCheckout(PaymentMethodInterface $paymentMethod)
    {
        $PaymentResult = $paymentMethod->checkout();
        $response = $PaymentResult->getResponse();
        // PaymentResultがresponseを保持している場合はresponseを返す
        if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
            $this->entityManager->flush();
            log_info('[注文処理] PaymentMethod::checkoutが指定したレスポンスを表示します.');

            return $response;
        }

        // エラー時はロールバックして購入エラーとする.
        if (!$PaymentResult->isSuccess()) {
            $this->entityManager->rollback();
            foreach ($PaymentResult->getErrors() as $error) {
                $this->addError($error);
            }

            log_info('[注文処理] PaymentMethod::checkoutのエラーのため, 購入エラー画面へ遷移します.', [$PaymentResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }
    }
}
