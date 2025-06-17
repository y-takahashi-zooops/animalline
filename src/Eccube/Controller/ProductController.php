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

namespace Eccube\Controller;

use Eccube\Entity\BaseInfo;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\AddCartType;
use Eccube\Form\Type\Master\ProductListMaxType;
use Eccube\Form\Type\Master\ProductListOrderByType;
use Eccube\Form\Type\SearchProductType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\Master\ProductListMaxRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\FormFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Category;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ProductController extends AbstractController
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
            $eccubeConfig,
            $entityManager,
            $translator,
            $session,
            $formFactory,
            $eventDispatcher,
            $requestStack,
            $router
        );

        $this->purchaseFlow = $cartPurchaseFlow;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->helper = $helper;
        $this->productListMaxRepository = $productListMaxRepository;
        $this->logger = $logger;
    }

    /**
     * 商品一覧画面.
     *
     * @Route("/products/list", name="product_list")
     * @Template("Product/list.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        // Doctrine SQLFilter
        if ($this->BaseInfo->isOptionNostockHidden()) {
            $this->entityManager->getFilters()->enable('option_nostock_hidden');
        }

        // handleRequestは空のqueryの場合は無視するため
        if ($request->getMethod() === 'GET') {
            $request->query->set('pageno', $request->query->get('pageno', ''));
        }

        // searchForm
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createNamedBuilder('', SearchProductType::class);

        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_INDEX_INITIALIZE);

        /* @var $searchForm \Symfony\Component\Form\FormInterface */
        $searchForm = $builder->getForm();

        $searchForm->handleRequest($request);

        // paginator
        $searchData = $searchForm->getData();
        $qb = $this->productRepository->getQueryBuilderBySearchData($searchData, $this->getUser());

        $event = new EventArgs(
            [
                'searchData' => $searchData,
                'qb' => $qb,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_INDEX_SEARCH);
        $searchData = $event->getArgument('searchData');

        $query = $qb->getQuery()
            ->useResultCache(true, $this->eccubeConfig['eccube_result_cache_lifetime_short']);

        /** @var SlidingPagination $pagination */
        $dispNumberId = is_object($searchData['disp_number'] ?? null)
            ? $searchData['disp_number']->getId()
            : ($searchData['disp_number'] ?? null);

        $pagination = $paginator->paginate(
            $query,
            !empty($searchData['pageno']) ? $searchData['pageno'] : 1,
            $dispNumberId ?: $this->productListMaxRepository->findOneBy([], ['sort_no' => 'ASC'])->getId()
        );

        $ids = [];
        foreach ($pagination as $Product) {
            $ids[] = $Product->getId();
        }
        $ProductsAndClassCategories = $this->productRepository->findProductsWithSortedClassCategories($ids, 'p.id');

        // addCart form
        $forms = [];
        foreach ($pagination as $Product) {
            /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
            $builder = $this->formFactory->createNamedBuilder(
                'add_cart',
                AddCartType::class,
                null,
                [
                    'product' => $ProductsAndClassCategories[$Product->getId()],
                    'allow_extra_fields' => true,
                ]
            );
            $addCartForm = $builder->getForm();

            $forms[$Product->getId()] = $addCartForm->createView();
        }

        // 表示件数
        $builder = $this->formFactory->createNamedBuilder(
            'disp_number',
            ProductListMaxType::class,
            null,
            [
                'required' => false,
                'allow_extra_fields' => true,
            ]
        );
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_INDEX_DISP);

        $dispNumberForm = $builder->getForm();

        $dispNumberForm->handleRequest($request);

        // ソート順
        $builder = $this->formFactory->createNamedBuilder(
            'orderby',
            ProductListOrderByType::class,
            null,
            [
                'required' => false,
                'allow_extra_fields' => true,
            ]
        );
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_INDEX_ORDER);

        $orderByForm = $builder->getForm();

        $orderByForm->handleRequest($request);

        $Category = $searchForm->get('category_id')->getData();

        // JSON翻訳
        $productsClassCategories = [];

        foreach ($ProductsAndClassCategories as $Product) {
            $id = $Product->getId();
            $productsClassCategories[$id] = [];

            foreach ($Product->getProductClasses() as $ProductClass) {
                if (!$ProductClass->isVisible()) {
                    continue;
                }

                $ClassCategory1 = $ProductClass->getClassCategory1();
                $ClassCategory2 = $ProductClass->getClassCategory2();

                if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                    continue;
                }

                $id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected';
                $id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';

                if (!isset($productsClassCategories[$id][$id1])) {
                    $productsClassCategories[$id][$id1] = [];
                }

                $productsClassCategories[$id][$id1][$id2] = [
                    'classcategory_id2' => $id2,
                    'name' => $ClassCategory2 ? $ClassCategory2->getName() : $this->translator->trans('common.select'),
                    'product_class_id' => (string) $ProductClass->getId(),
                ];
            }
        }

        return [
            'subtitle' => $this->getPageTitle($searchData),
            'pagination' => $pagination,
            'search_form' => $searchForm->createView(),
            'disp_number_form' => $dispNumberForm->createView(),
            'order_by_form' => $orderByForm->createView(),
            'forms' => $forms,
            'Category' => $Category,
            'products_class_categories_json' => json_encode($productsClassCategories, JSON_UNESCAPED_UNICODE)
        ];
    }


    /**
     * 商品詳細画面.
     *
     * @Route("/products/detail/{id}", name="product_detail", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("Product/detail.twig")
     * @ParamConverter("Product", options={"repository_method" = "findWithSortedClassCategories"})
     *
     * @param Request $request
     * @param Product $Product
     *
     * @return array
     */
    public function detail(Request $request, Product $Product)
    {
        if (!$this->checkVisibility($Product)) {
            throw new NotFoundHttpException();
        }

        $Product->_calc();

        $builder = $this->formFactory->createNamedBuilder(
            'add_cart',
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
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_DETAIL_INITIALIZE);

        $is_favorite = false;
        if ($this->isGranted('ROLE_USER')) {
            $Customer = $this->getUser();
            $is_favorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);
        }

        // JSONの読み込み
        $classCategories = [
            '__unselected' => [
                '__unselected' => [
                    'name' => $this->translator->trans('common.select'),
                    'product_class_id' => '',
                ],
            ],
        ];

        foreach ($Product->getProductClasses() as $ProductClass) {
            if (!$ProductClass->isVisible()) {
                continue;
            }

            $ClassCategory1 = $ProductClass->getClassCategory1();
            $ClassCategory2 = $ProductClass->getClassCategory2();
            if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                continue;
            }

            $id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
            $id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';

            $name2 = $ClassCategory2
                ? $ClassCategory2->getName() . (!$ProductClass->getStockFind() ? ' ' . $this->translator->trans('front.product.out_of_stock_label') : '')
                : $this->translator->trans('common.select');

            // 初期選択肢（セレクトボックスの「選択してください」）
            if (!isset($classCategories[$id1][''])) {
                $classCategories[$id1]['#'] = [
                    'classcategory_id2' => '',
                    'name' => $this->translator->trans('common.select'),
                    'product_class_id' => '',
                ];
            }

            $classCategories[$id1][$id2] = [
                'classcategory_id2' => $id2,
                'name' => $name2,
                'stock_find' => $ProductClass->getStockFind(),
                'price01' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01()),
                'price02' => number_format($ProductClass->getPrice02()),
                'price01_inc_tax' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01IncTax()),
                'price02_inc_tax' => number_format($ProductClass->getPrice02IncTax()),
                'product_class_id' => (string) $ProductClass->getId(),
                'product_code' => $ProductClass->getCode() ?? '',
                'sale_type' => $ProductClass->getSaleType() ? (string) $ProductClass->getSaleType()->getId() : '',
                'item_cost' => method_exists($ProductClass, 'getItemCost') ? (float) $ProductClass->getItemCost() : 0.0,
            ];
        }

        $classCategoriesJson = json_encode($classCategories, JSON_UNESCAPED_UNICODE);

        $is_favorite = false;
        if ($this->isGranted('ROLE_USER')) {
            $Customer = $this->getUser();
            $is_favorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);
        }


        return [
            'title' => $this->title,
            'subtitle' => $Product->getName(),
            'form' => $builder->getForm()->createView(),
            'Product' => $Product,
            'is_favorite' => $is_favorite,
            'class_categories_json' => $classCategoriesJson,
        ];
    }

    /**
     * お気に入り追加.
     *
     * @Route("/products/add_favorite/{id}", name="product_add_favorite", requirements={"id" = "\d+"})
     */
    public function addFavorite(Request $request, Product $Product)
    {
        $this->checkVisibility($Product);

        $event = new EventArgs(
            [
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_INITIALIZE);

        if ($this->isGranted('ROLE_USER')) {
            $Customer = $this->getUser();
            $this->customerFavoriteProductRepository->addFavorite($Customer, $Product);
            $this->session->getFlashBag()->set('product_detail.just_added_favorite', $Product->getId());

            $event = new EventArgs(
                [
                    'Product' => $Product,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE);

            return $this->redirectToRoute('product_detail', ['id' => $Product->getId()]);
        } else {
            // 非会員の場合、ログイン画面を表示
            //  ログイン後の画面遷移先を設定
            $this->setLoginTargetPath($this->generateUrl('product_add_favorite', ['id' => $Product->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
            $this->session->getFlashBag()->set('eccube.add.favorite', true);

            $event = new EventArgs(
                [
                    'Product' => $Product,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE);

            return $this->redirectToRoute('mypage_login');
        }
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

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new NotFoundHttpException();
        }

        $addCartData = $form->getData();

        $this->logger->info(
            'カート追加処理開始',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $addCartData['quantity'],
            ]
        );

        // カートへ追加
        $this->cartService->addProduct($addCartData['product_class_id'], $addCartData['quantity']);

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

    /**
     * ページタイトルの設定
     *
     * @param  null|array $searchData
     *
     * @return str
     */
    protected function getPageTitle($searchData)
    {
        if (isset($searchData['name']) && !empty($searchData['name'])) {
            return $this->translator->trans('front.product.search_result');
        } elseif (isset($searchData['category_id']) && $searchData['category_id']) {
            return $searchData['category_id']->getName();
        } else {
            return $this->translator->trans('front.product.all_products');
        }
    }

    /**
     * 閲覧可能な商品かどうかを判定
     *
     * @param Product $Product
     *
     * @return boolean 閲覧可能な場合はtrue
     */
    protected function checkVisibility(Product $Product)
    {
        $is_admin = $this->session->has('_security_admin');

        // 管理ユーザの場合はステータスやオプションにかかわらず閲覧可能.
        if (!$is_admin) {
            // 在庫なし商品の非表示オプションが有効な場合.
            // if ($this->BaseInfo->isOptionNostockHidden()) {
            //     if (!$Product->getStockFind()) {
            //         return false;
            //     }
            // }
            // 公開ステータスでない商品は表示しない.
            if ($Product->getStatus()->getId() !== ProductStatus::DISPLAY_SHOW) {
                return false;
            }
        }

        return true;
    }
}
