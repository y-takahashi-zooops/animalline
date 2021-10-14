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

use Eccube\Controller\Mypage\MypageController as BaseMypageController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Order;
use Eccube\Entity\CustomerAddress;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ShoppingShippingType;
use Customize\Form\Type\SubscriptionCustomerAddressType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CustomerAddressRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Plugin\ZooopsSubscription\Repository\SubscriptionContractRepository;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Repository\PaymentRepository;
use Eccube\Repository\DeliveryFeeRepository;
use Eccube\Repository\ProductClassRepository;
use Customize\Form\Type\ChangeSubscriptionType;
use Customize\Service\SubscriptionProcess;
use Eccube\Repository\ShippingRepository;

class MypageController extends BaseMypageController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var SubscriptionContractRepository
     */
    protected $subscriptionContractRepository;

    /**
     * @var TaxRuleRepository
     */
    protected $taxRuleRepository;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var DeliveryFeeRepository
     */
    protected $deliveryFeeRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var SubscriptionProcess
     */
    protected $SubscriptionProcess;

    /**
     * @var CustomerAddressRepository
     */
    protected $customerAddressRepository;

    /**
     * @var ShippingRepository
     */
    protected $shippingRepository;

    /**
     * MypageController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param CartService $cartService
     * @param BaseInfoRepository $baseInfoRepository
     * @param PurchaseFlow $purchaseFlow
     * @param SubscriptionContract $subscriptionContract
     * @param TaxRuleRepository $taxRuleRepository
     * @param PaymentRepository $paymentRepository
     * @param DeliveryFeeRepository $deliveryFeeRepository
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param SubscriptionProcess $SubscriptionProcess
     * @param CustomerAddressRepository $customerAddressRepository
     * @param ShippingRepository $shippingRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        CartService $cartService,
        BaseInfoRepository $baseInfoRepository,
        PurchaseFlow $purchaseFlow,
        SubscriptionContractRepository $subscriptionContractRepository,
        TaxRuleRepository $taxRuleRepository,
        PaymentRepository $paymentRepository,
        DeliveryFeeRepository $deliveryFeeRepository,
        ProductClassRepository $productClassRepository,
        ProductRepository $productRepository,
        SubscriptionProcess $SubscriptionProcess,
        CustomerAddressRepository $customerAddressRepository,
        ShippingRepository $shippingRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->cartService = $cartService;
        $this->purchaseFlow = $purchaseFlow;
        $this->subscriptionContractRepository = $subscriptionContractRepository;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->paymentRepository = $paymentRepository;
        $this->deliveryFeeRepository = $deliveryFeeRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->SubscriptionProcess = $SubscriptionProcess;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->shippingRepository = $shippingRepository;
    }

    /**
     * 定期購入管理.
     *
     * @Route("/mypage/subscription", name="mypage_subscription")
     * @Template("Mypage/subscription.twig")
     */
    public function subscription(Request $request, Paginator $paginator)
    {
        $Customer = $this->getUser();

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager
            ->getFilters()
            ->enable('incomplete_order_status_hidden');

        $SubscriptionContracts = array();

        // ログインユーザーの定期注文を全取得
        $SubscriptionContract = $this->subscriptionContractRepository->findBy(['Customer' => $Customer]);

        foreach ($SubscriptionContract as $sc) {
            // 契約日が空欄でないもののみを対象とする。
            if ($sc->getContractDate() !== null) {
                array_push($SubscriptionContracts, $sc);
            }
        }

        // paginator
        // $qb = $this->orderRepository->getQueryBuilderByCustomer($Customer);
        $qb = $this->subscriptionContractRepository->getQueryBuilderByCustomer($Customer);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch('front.mypage.mypage.subscription.history.initialize', $event);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax']
        );

        return [
            'pagination' => $pagination,
            'SubscriptionContracts' => $SubscriptionContracts,
        ];
    }

    /**
     * 定期購入管理/購入履歴詳細を表示する.
     *
     * @Route("/mypage/subscription/history/{id}", name="mypage_subscription_history")
     * @Template("Mypage/subscription_history.twig")
     */
    public function subscription_history(Request $request, $id)
    {
        $this->entityManager->getFilters()
            ->enable('incomplete_order_status_hidden');
        $SubscriptionContract = $this->subscriptionContractRepository->findOneBy(
            [
                'id' => $id,
                'Customer' => $this->getUser(),
            ]
        );

        $event = new EventArgs(
            [
                'SubscriptionContract' => $SubscriptionContract,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_HISTORY_INITIALIZE, $event);

        /** @var Order $Order */
        $SubscriptionContract = $event->getArgument('SubscriptionContract');
        // $Order = $this->orderRepository->findOneBy(['SubscriptionContract' => $SubscriptionContract]);
        $Order = $SubscriptionContract->getOrder();
        $Shipping = $this->shippingRepository->findOneBy(['Order' => $Order]);

        if (!$SubscriptionContract) {
            throw new NotFoundHttpException();
        }

        // 個数
        $quantity = $SubscriptionContract->getQuantity();

        // 小計
        $subtotal = $SubscriptionContract->getProduct()->getPrice02IncTaxMax();
        $subtotal = $subtotal * $quantity;

        // 消費税率
        $taxRate = $this->taxRuleRepository->getByRule()->getTaxRate();

        // 手数料
        $charge = $this->paymentRepository->findOneBy(['id' => $Order->getPayment()])->getCharge();
        // 送料
        if ($this->BaseInfo->isOptionProductDeliveryFee()) {
            $deliveryFee = $this->productClassRepository->findOneBy(['id' => $SubscriptionContract->getProductClass()])->getDeliveryFee();
        } else {
            // TODO:deliveryFeeの取得
            $deliveryFee = $this->deliveryFeeRepository->findOneBy(['Delivery' => $Shipping->getDelivery(), 'Pref' => $Shipping->getPref()])->getFee();

            if ($this->BaseInfo->getDeliveryFreeAmount()) {
                if ($subtotal > $this->BaseInfo->getDeliveryFreeAmount()) {
                    $deliveryFee = 0;
                }
            }

            if ($this->BaseInfo->getDeliveryFreeQuantity()) {
                if ($SubscriptionContract->getQuantity() > $this->BaseInfo->getDeliveryFreeQuantity()) {
                    $deliveryFee = 0;
                }
            }
        }
        // 合計
        $total = $subtotal + $charge + $deliveryFee;

        // 住所
        $CustomerAddressId = $SubscriptionContract->getCustomerAddressId();
        if( $CustomerAddressId ){
            $CustomerAddress = $this->customerAddressRepository->find($CustomerAddressId);
        } else {
            $CustomerAddress = $Shipping;
        }

        $form = $this->createForm(ChangeSubscriptionType::class, $SubscriptionContract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            // 定期購入情報変更
            if (isset($_POST['change'])) {
                $this->SubscriptionProcess
                    ->ChangeSubscription($formData['repeat_span'], $formData['span_unit'], $formData['next_delivery_date'], $SubscriptionContract);
                // 定期注文停止
            } elseif (isset($_POST['cancel'])) {
                $this->SubscriptionProcess->StopSubscription($SubscriptionContract);
            }

            return $this->redirectToRoute('mypage_subscription_history', array('id' => $id));
        }

        return [
            'SubscriptionContract' => $SubscriptionContract,
            'Order' => $Order,
            'CustomerAddress' => $CustomerAddress,
            'Product' => $SubscriptionContract->getProduct(),
            'subtotal' => $subtotal,
            'taxRate' => $taxRate,
            'charge' => $charge,
            'deliveryFee' => $deliveryFee,
            'total' => $total,
            'form' => $form->createView(),
        ];
    }

    /**
     * 定期購入お届け先選択画面.
     *
     * お届け先を選択する画面を表示する
     *
     * @Route("/mypage/subscription/history/shipping/{id}", name="mypage_subscription_shipping", requirements={"id" = "\d+"})
     * @Template("Shopping/subscription_shipping.twig")
     */
    public function shipping(Request $request, $id)
    {
        $SubscriptionContract = $this->subscriptionContractRepository->find($id);

        $builder = $this->formFactory->createBuilder(SubscriptionCustomerAddressType::class, null, [
            'customer' => $this->getUser(),
            'subscriptionContract' => $SubscriptionContract,
        ]);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var CustomerAddress $CustomerAddress */
            $CustomerAddress = $form['addresses']->getData();
            $CustomerAddressId = $CustomerAddress->getId();

            // お届け先情報を更新
            $SubscriptionContract->setCustomerAddressId($CustomerAddressId);

            $this->entityManager->flush();

            // TODO:成功メッセージが出ない
            $this->addSuccess('お届け先を変更しました');
            return $this->redirectToRoute('mypage_subscription_history', array('id' => $id));
        }

        return [
            'form' => $form->createView(),
            'Customer' => $this->getUser(),
            'Id' => $SubscriptionContract->getId(),
        ];
    }

    /**
     * 定期購入お届け先の新規作成または編集画面.
     *
     * 会員時は新しいお届け先を作成し, 作成したお届け先を選択状態にして注文手続き画面へ遷移する.
     * 非会員時は選択されたお届け先の編集を行う.
     *
     * @Route("/mypage/subscription/history/shipping_edit/{id}", name="mypage_subscription_shipping_edit", requirements={"id" = "\d+"})
     * @Template("Shopping/subscription_shipping_edit.twig")
     */
    public function shippingEdit(Request $request, $id)
    {
        $SubscriptionContract = $this->subscriptionContractRepository->find($id);

        $CustomerAddress = new CustomerAddress();
        $CustomerAddress->setCustomer($this->getUser());

        $builder = $this->formFactory->createBuilder(ShoppingShippingType::class, $CustomerAddress);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'CustomerAddress' => $CustomerAddress,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_SHIPPING_EDIT_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 新規お届け先レコードを作成
            $this->entityManager->persist($CustomerAddress);
            $this->entityManager->flush();

            // 定期注文テーブルのお届け先を変更
            $SubscriptionContract->setCustomerAddressId($CustomerAddress->getId());
            $this->entityManager->persist($SubscriptionContract);
            $this->entityManager->flush();
            
            // TODO:成功メッセージが出ない
            $this->addSuccess('お届け先を変更しました');

            return $this->redirectToRoute('mypage_subscription_history', array('id' => $id));
        }

        return [
            'form' => $form->createView(),
            'Id' => $SubscriptionContract->getId(),
        ];
    }


 
}
