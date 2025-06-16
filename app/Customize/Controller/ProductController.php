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

use Eccube\Entity\BaseInfo;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\AddCartType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\Master\ProductListMaxRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Controller\ProductController as BaseProductController;
use Eccube\Repository\CartItemRepository;
use Eccube\Repository\CartRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ProductController extends BaseProductController
{
    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var AuthenticationUtils
     */
    protected $helper;

    /**
     * @var ProductListMaxRepository
     */
    protected $productListMaxRepository;

    /**
     * @var CartItemRepository
     */
    protected $cartItemRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SessionInterface
     */
    protected SessionInterface $session;

    private $title = '';

    /**
     * ProductController constructor.
     *
     * @param PurchaseFlow $cartPurchaseFlow
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param CartService $cartService
     * @param ProductRepository $productRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param AuthenticationUtils $helper
     * @param ProductListMaxRepository $productListMaxRepository
     * @param CartItemRepository $cartItemRepository
     * @param CartRepository $cartRepository
     * @param LoggerInterface $logger
     * @param SessionInterface $session
     */
    public function __construct(
        PurchaseFlow $cartPurchaseFlow,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        CartService $cartService,
        ProductRepository $productRepository,
        BaseInfoRepository $baseInfoRepository,
        AuthenticationUtils $helper,
        ProductListMaxRepository $productListMaxRepository,
        CartItemRepository $cartItemRepository,
        CartRepository $cartRepository,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        SessionInterface $session,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        RouterInterface $router        
    ) {
        parent::__construct(
            $cartPurchaseFlow,
            $customerFavoriteProductRepository,
            $cartService,
            $productRepository,
            $baseInfoRepository,
            $helper,
            $productListMaxRepository,
            $formFactory,
            $logger,
            $session,
            $eventDispatcher,
            $entityManager,
            $eccubeConfig,
            $translator,
            $requestStack,
            $router
        );

        $this->cartItemRepository = $cartItemRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * カートに追加.
     *
     * @Route("/products/add_cart/{id}", name="product_add_cart", methods={"POST"}, requirements={"id" = "\d+"})
     */
    public function addCart(Request $request, Product $Product)
    {
        // エラーメッセージの配列
        $errorMessages = [];
        if (!$this->checkVisibility($Product)) {
            throw new NotFoundHttpException();
        }

        $builder = $this->formFactory->createNamedBuilder(
            '',
            AddCartType::class,
            null,
            [
                'product' => $Product,
                'id_add_product_id' => false,
            ]
        );

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_CART_ADD_INITIALIZE);

        // $is_favorite = false;
        // if ($this->isGranted('ROLE_USER')) {
        //     $Customer = $this->getUser();
        //     $is_favorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);
        // }

        // // JSONの成型
        // $classCategories = [
        //     '__unselected' => [
        //         '__unselected' => [
        //             'name' => $this->translator->trans('common.select'),
        //             'product_class_id' => '',
        //         ],
        //     ],
        // ];

        // foreach ($Product->getProductClasses() as $ProductClass) {
        //     if (!$ProductClass->isVisible()) {
        //         continue;
        //     }

        //     $ClassCategory1 = $ProductClass->getClassCategory1();
        //     $ClassCategory2 = $ProductClass->getClassCategory2();
        //     if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
        //         continue;
        //     }

        //     $id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
        //     $id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';

        //     $name2 = $ClassCategory2
        //         ? $ClassCategory2->getName() . (!$ProductClass->getStockFind() ? ' ' . $this->translator->trans('front.product.out_of_stock_label') : '')
        //         : $this->translator->trans('common.select');

        //     if (!isset($classCategories[$id1][''])) {
        //         $classCategories[$id1]['#'] = [
        //             'classcategory_id2' => '',
        //             'name' => $this->translator->trans('common.select'),
        //             'product_class_id' => '',
        //         ];
        //     }

        //     $classCategories[$id1][$id2] = [
        //         'classcategory_id2' => $id2,
        //         'name' => $name2,
        //         'stock_find' => $ProductClass->getStockFind(),
        //         'price01' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01()),
        //         'price02' => number_format($ProductClass->getPrice02()),
        //         'price01_inc_tax' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01IncTax()),
        //         'price02_inc_tax' => number_format($ProductClass->getPrice02IncTax()),
        //         'product_class_id' => (string) $ProductClass->getId(),
        //         'product_code' => $ProductClass->getCode() ?? '',
        //         'sale_type' => $ProductClass->getSaleType() ? (string) $ProductClass->getSaleType()->getId() : '',
        //         'item_cost' => method_exists($ProductClass, 'getItemCost') ? (float) $ProductClass->getItemCost() : 0.0,
        //     ];
        // }

        // $classCategoriesJson = json_encode($classCategories, JSON_UNESCAPED_UNICODE);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $this->logger->error('フォームバリデーションエラー', [
                'errors' => (string) $form->getErrors(true, false),
            ]);

            return $this->render('Product/detail.twig', [
                'form' => $form->createView(),
                'Product' => $Product,
                'BaseInfo' => $this->BaseInfo,
                'errorMessages' => ['入力内容に誤りがあります。'],
                'is_favorite' => $is_favorite,
                'class_categories_json' => $classCategoriesJson,
            ]);
        }

        // if (!$form->isValid()) {
        //     throw new NotFoundHttpException();
        // }

        $addCartData = $form->getData();

        // カート商品に同商品がないか検索
        $cartItem = $this->cartItemRepository->findOneBy(['Cart' => $this->cartService->getCarts(),'ProductClass' => $addCartData['product_class_id']]);
        if ($cartItem) {
            // 同商品がカートに存在したらカートに追加させない
            return;
        }

        $this->logger->info(
            'カート追加処理開始',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $addCartData['quantity'],
                'is_repeat' => $addCartData['is_repeat'],
                'repeat_span' => $addCartData['repeat_span'],
                'span_unit' => $addCartData['span_unit'],
                // 'is_favorite' => $is_favorite,
            ]
        );

        $this->cartService->addProduct(
            $addCartData['product_class_id'],
            $addCartData['quantity'],
            $addCartData['is_repeat'],
            $addCartData['repeat_span'],
            $addCartData['span_unit']
        );

        // 明細の正規化
        $Carts = $this->cartService->getCarts();
        foreach ($Carts as $Cart) {
            $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
            // 復旧不可のエラーが発生した場合は追加した明細を削除.
            if ($result->hasError()) {
                $this->cartService->removeProduct($addCartData['product_class_id']);
                foreach ($result->getErrors() as $error) {
                    $errorMessages[] = $error->getMessage();
                }
            }
            foreach ($result->getWarning() as $warning) {
                $errorMessages[] = $warning->getMessage();
            }
        }

        $this->cartService->save();

        $this->logger->info(
            'カート追加処理完了',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $addCartData['quantity'],
                'is_repeat' => $addCartData['is_repeat'],
                'repeat_span' => $addCartData['repeat_span'],
                'span_unit' => $addCartData['span_unit'],
            ]
        );

        $event = new EventArgs(
            [
                'form' => $form,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_CART_ADD_COMPLETE);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        if ($request->isXmlHttpRequest()) {
            // ajaxでのリクエストの場合は結果をjson形式で返す。

            // 初期化
            $done = null;
            $messages = [];

            if (empty($errorMessages)) {
                // エラーが発生していない場合
                $done = true;
                array_push($messages, trans('front.product.add_cart_complete'));
            } else {
                // エラーが発生している場合
                $done = false;
                $messages = $errorMessages;
            }

            return $this->json(['done' => $done, 'messages' => $messages]);
        } else {
            // ajax以外でのリクエストの場合はカート画面へリダイレクト
            foreach ($errorMessages as $errorMessage) {
                $this->addRequestError($errorMessage);
            }

            return $this->redirectToRoute('cart');
        }
    }
}
