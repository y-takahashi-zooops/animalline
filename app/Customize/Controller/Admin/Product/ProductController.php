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

namespace Customize\Controller\Admin\Product;

use Customize\Entity\InstockScheduleHeader;
use Customize\Entity\StockWaste;
use Customize\Repository\StockWasteReasonRepository;
use Customize\Repository\StockWasteRepository;
use Customize\Service\GetListWasteQueryService;
use Doctrine\Common\Collections\ArrayCollection;
use Customize\Config\AnilineConf;
use Customize\Form\Type\Admin\InstockScheduleHeaderType;
use Customize\Repository\InstockScheduleHeaderRepository;
use Customize\Repository\InstockScheduleRepository;
use Customize\Service\ListInstockQueryService;
use Customize\Entity\InstockSchedule;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductTag;
use Customize\Entity\Supplier;
use Customize\Form\Type\Admin\StockWasteType;
use Customize\Form\Type\Admin\SupplierType;
use Customize\Repository\SupplierRepository;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\ProductType;
use Eccube\Form\Type\Admin\SearchProductType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductImageRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\TagRepository;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Service\CsvExportService;
use Eccube\Util\CacheUtil;
use Eccube\Util\FormUtil;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Eccube\Controller\Admin\Product\ProductController as BaseProductController;
use Eccube\Entity\OrderItem;
use Eccube\Repository\Master\OrderItemTypeRepository;

class ProductController extends BaseProductController
{
    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ProductImageRepository
     */
    protected $productImageRepository;

    /**
     * @var TaxRuleRepository
     */
    protected $taxRuleRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var InstockScheduleHeaderRepository
     */
    protected $instockScheduleHeaderRepository;

    /**
     * @var InstockScheduleRepository
     */
    protected $instockScheduleRepository;

    /**
     * @var ListInstockQueryService
     */
    protected $listInstockQueryService;

    /**
     * @var OrderItemTypeRepository
     */
    protected $orderItemTypeRepository;

    /**
     * @var StockWasteRepository
     */
    protected $stockWasteRepository;

    /**
     * @var StockWasteReasonRepository
     */
    protected $stockWasteReasonRepository;

    /**
     * @var GetListWasteQueryService
     */
    protected $getListWasteQueryService;

    /**
     * ProductController constructor.
     *
     * @param CsvExportService $csvExportService
     * @param ProductClassRepository $productClassRepository
     * @param ProductImageRepository $productImageRepository
     * @param TaxRuleRepository $taxRuleRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param PageMaxRepository $pageMaxRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param TagRepository $tagRepository
     * @param SupplierRepository $supplierRepository
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     * @param ListInstockQueryService $listInstockQueryService
     * @param OrderItemTypeRepository $orderItemTypeRepository
     * @param StockWasteRepository $stockWasteRepository
     * @param StockWasteReasonRepository $stockWasteReasonRepository
     * @param GetListWasteQueryService $getListWasteQueryService
     */
    public function __construct(
        CsvExportService                $csvExportService,
        ProductClassRepository          $productClassRepository,
        ProductImageRepository          $productImageRepository,
        TaxRuleRepository               $taxRuleRepository,
        CategoryRepository              $categoryRepository,
        ProductRepository               $productRepository,
        BaseInfoRepository              $baseInfoRepository,
        PageMaxRepository               $pageMaxRepository,
        ProductStatusRepository         $productStatusRepository,
        TagRepository                   $tagRepository,
        SupplierRepository              $supplierRepository,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository,
        ListInstockQueryService         $listInstockQueryService,
        OrderItemTypeRepository         $orderItemTypeRepository,
        StockWasteRepository            $stockWasteRepository,
        StockWasteReasonRepository      $stockWasteReasonRepository,
        GetListWasteQueryService        $getListWasteQueryService
    ) {
        $this->csvExportService = $csvExportService;
        $this->productClassRepository = $productClassRepository;
        $this->productImageRepository = $productImageRepository;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->pageMaxRepository = $pageMaxRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->tagRepository = $tagRepository;
        $this->supplierRepository = $supplierRepository;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
        $this->listInstockQueryService = $listInstockQueryService;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
        $this->stockWasteRepository = $stockWasteRepository;
        $this->stockWasteReasonRepository = $stockWasteReasonRepository;
        $this->getListWasteQueryService = $getListWasteQueryService;
        $this->stockWasteReasonRepository = $stockWasteReasonRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/product", name="admin_product")
     * @Route("/%eccube_admin_route%/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_page")
     * @Template("@admin/Product/index.twig")
     */
    public function index(Request $request, $page_no = null, Paginator $paginator)
    {
        $builder = $this->formFactory
            ->createBuilder(SearchProductType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_INITIALIZE, $event);

        $searchForm = $builder->getForm();

        /**
         * ページの表示件数は, 以下の順に優先される.
         * - リクエストパラメータ
         * - セッション
         * - デフォルト値
         * また, セッションに保存する際は mtb_page_maxと照合し, 一致した場合のみ保存する.
         **/
        $page_count = $this->session->get(
            'eccube.admin.product.search.page_count',
            $this->eccubeConfig->get('eccube_default_page_count')
        );

        $page_count_param = (int)$request->get('page_count');
        $pageMaxis = $this->pageMaxRepository->findAll();

        if ($page_count_param) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    $this->session->set('eccube.admin.product.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                /**
                 * 検索が実行された場合は, セッションに検索条件を保存する.
                 * ページ番号は最初のページ番号に初期化する.
                 */
                $page_no = 1;
                $searchData = $searchForm->getData();

                // 検索条件, ページ番号をセッションに保持.
                $this->session->set('eccube.admin.product.search', FormUtil::getViewData($searchForm));
                $this->session->set('eccube.admin.product.search.page_no', $page_no);
            } else {
                // 検索エラーの際は, 詳細検索枠を開いてエラー表示する.
                return [
                    'searchForm' => $searchForm->createView(),
                    'pagination' => [],
                    'pageMaxis' => $pageMaxis,
                    'page_no' => $page_no,
                    'page_count' => $page_count,
                    'has_errors' => true,
                ];
            }
        } else {
            if (null !== $page_no || $request->get('resume')) {
                /*
                 * ページ送りの場合または、他画面から戻ってきた場合は, セッションから検索条件を復旧する.
                 */
                if ($page_no) {
                    // ページ送りで遷移した場合.
                    $this->session->set('eccube.admin.product.search.page_no', (int)$page_no);
                } else {
                    // 他画面から遷移した場合.
                    $page_no = $this->session->get('eccube.admin.product.search.page_no', 1);
                }
                $viewData = $this->session->get('eccube.admin.product.search', []);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
            } else {
                /**
                 * 初期表示の場合.
                 */
                $page_no = 1;
                // submit default value
                $viewData = FormUtil::getViewData($searchForm);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

                // セッション中の検索条件, ページ番号を初期化.
                $this->session->set('eccube.admin.product.search', $viewData);
                $this->session->set('eccube.admin.product.search.page_no', $page_no);
            }
        }

        $qb = $this->productRepository->getQueryBuilderBySearchDataForAdmin($searchData);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'searchData' => $searchData,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH, $event);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'has_errors' => false,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/product/classes/{id}/load", name="admin_product_classes_load", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("@admin/Product/product_class_popup.twig")
     * @ParamConverter("Product")
     */
    public function loadProductClasses(Request $request, Product $Product)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $data = [];
        /** @var $Product ProductRepository */
        if (!$Product) {
            throw new NotFoundHttpException();
        }

        if ($Product->hasProductClass()) {
            $class = $Product->getProductClasses();
            foreach ($class as $item) {
                if ($item['visible']) {
                    $data[] = $item;
                }
            }
        }

        return [
            'data' => $data,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/product/product/image/add", name="admin_product_image_add", methods={"POST"})
     */
    public function addImage(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $images = $request->files->get('admin_product');

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];
        $files = [];
        if (count($images) > 0) {
            foreach ($images as $img) {
                foreach ($img as $image) {
                    //ファイルフォーマット検証
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    // 拡張子
                    $extension = $image->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), $allowExtensions)) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    $filename = date('mdHis') . uniqid('_') . '.' . $extension;
                    $image->move($this->eccubeConfig['eccube_temp_image_dir'], $filename);
                    $files[] = $filename;
                }
            }
        }

        $event = new EventArgs(
            [
                'images' => $images,
                'files' => $files,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_ADD_IMAGE_COMPLETE, $event);
        $files = $event->getArgument('files');

        return $this->json(['files' => $files], 200);
    }

    /**
     * @Route("/%eccube_admin_route%/product/product/new", name="admin_product_product_new")
     * @Route("/%eccube_admin_route%/product/product/{id}/edit", requirements={"id" = "\d+"}, name="admin_product_product_edit")
     * @Template("@admin/Product/product.twig")
     */
    public function edit(Request $request, $id = null, RouterInterface $router, CacheUtil $cacheUtil)
    {
        $has_class = false;
        if (is_null($id)) {
            $Product = new Product();
            $ProductClass = new ProductClass();
            $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_HIDE);
            $Product
                ->addProductClass($ProductClass)
                ->setStatus($ProductStatus);
            $ProductClass
                ->setVisible(true)
                ->setStockUnlimited(true)
                ->setProduct($Product);
            $ProductStock = new ProductStock();
            $ProductClass->setProductStock($ProductStock);
            $ProductStock->setProductClass($ProductClass);
        } else {
            $Product = $this->productRepository->find($id);
            if (!$Product) {
                throw new NotFoundHttpException();
            }
            // 規格無しの商品の場合は、デフォルト規格を表示用に取得する
            $has_class = $Product->hasProductClass();
            if (!$has_class) {
                $ProductClasses = $Product->getProductClasses();
                foreach ($ProductClasses as $pc) {
                    if (!is_null($pc->getClassCategory1())) {
                        continue;
                    }
                    if ($pc->isVisible()) {
                        $ProductClass = $pc;
                        break;
                    }
                }
                if ($this->BaseInfo->isOptionProductTaxRule() && $ProductClass->getTaxRule()) {
                    $ProductClass->setTaxRate($ProductClass->getTaxRule()->getTaxRate());
                }
                $ProductStock = $ProductClass->getProductStock();
            }
        }

        $builder = $this->formFactory
            ->createBuilder(ProductType::class, $Product);

        // 規格あり商品の場合、規格関連情報をFormから除外
        if ($has_class) {
            $builder->remove('class');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_EDIT_INITIALIZE, $event);

        $form = $builder->getForm();

        if (!$has_class) {
            $ProductClass->setStockUnlimited($ProductClass->isStockUnlimited());
            $form['class']->setData($ProductClass);
        }

        // ファイルの登録
        $images = [];
        $ProductImages = $Product->getProductImage();
        foreach ($ProductImages as $ProductImage) {
            $images[] = $ProductImage->getFileName();
        }
        $form['images']->setData($images);

        $categories = [];
        $ProductCategories = $Product->getProductCategories();
        foreach ($ProductCategories as $ProductCategory) {
            /* @var $ProductCategory \Eccube\Entity\ProductCategory */
            $categories[] = $ProductCategory->getCategory();
        }
        $form['Category']->setData($categories);

        $Tags = $Product->getTags();
        $form['Tag']->setData($Tags);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('商品登録開始', [$id]);
                $Product = $form->getData();

                if (!$has_class) {
                    $ProductClass = $form['class']->getData();

                    // 個別消費税
                    if ($this->BaseInfo->isOptionProductTaxRule()) {
                        if ($ProductClass->getTaxRate() !== null) {
                            if ($ProductClass->getTaxRule()) {
                                $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                            } else {
                                $taxrule = $this->taxRuleRepository->newTaxRule();
                                $taxrule->setTaxRate($ProductClass->getTaxRate());
                                $taxrule->setApplyDate(new \DateTime());
                                $taxrule->setProduct($Product);
                                $taxrule->setProductClass($ProductClass);
                                $ProductClass->setTaxRule($taxrule);
                            }

                            $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                        } else {
                            if ($ProductClass->getTaxRule()) {
                                $this->taxRuleRepository->delete($ProductClass->getTaxRule());
                                $ProductClass->setTaxRule(null);
                            }
                        }
                    }
                    $this->entityManager->persist($ProductClass);

                    // 在庫情報を作成
                    if (!$ProductClass->isStockUnlimited()) {
                        $ProductStock->setStock($ProductClass->getStock());
                    } else {
                        // 在庫無制限時はnullを設定
                        $ProductStock->setStock(null);
                    }
                    $this->entityManager->persist($ProductStock);
                }

                // カテゴリの登録
                // 一度クリア
                /* @var $Product \Eccube\Entity\Product */
                foreach ($Product->getProductCategories() as $ProductCategory) {
                    $Product->removeProductCategory($ProductCategory);
                    $this->entityManager->remove($ProductCategory);
                }
                $this->entityManager->persist($Product);
                $this->entityManager->flush();

                $count = 1;
                $Categories = $form->get('Category')->getData();
                $categoriesIdList = [];
                foreach ($Categories as $Category) {
                    foreach ($Category->getPath() as $ParentCategory) {
                        if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                            $ProductCategory = $this->createProductCategory($Product, $ParentCategory, $count);
                            $this->entityManager->persist($ProductCategory);
                            $count++;
                            /* @var $Product \Eccube\Entity\Product */
                            $Product->addProductCategory($ProductCategory);
                            $categoriesIdList[$ParentCategory->getId()] = true;
                        }
                    }
                    if (!isset($categoriesIdList[$Category->getId()])) {
                        $ProductCategory = $this->createProductCategory($Product, $Category, $count);
                        $this->entityManager->persist($ProductCategory);
                        $count++;
                        /* @var $Product \Eccube\Entity\Product */
                        $Product->addProductCategory($ProductCategory);
                        $categoriesIdList[$ParentCategory->getId()] = true;
                    }
                }

                // 画像の登録
                $add_images = $form->get('add_images')->getData();
                foreach ($add_images as $add_image) {
                    $ProductImage = new \Eccube\Entity\ProductImage();
                    $ProductImage
                        ->setFileName($add_image)
                        ->setProduct($Product)
                        ->setSortNo(1);
                    $Product->addProductImage($ProductImage);
                    $this->entityManager->persist($ProductImage);

                    // 移動
                    $file = new File($this->eccubeConfig['eccube_temp_image_dir'] . '/' . $add_image);
                    $file->move($this->eccubeConfig['eccube_save_image_dir']);
                }

                // 画像の削除
                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    $ProductImage = $this->productImageRepository
                        ->findOneBy(['file_name' => $delete_image]);

                    // 追加してすぐに削除した画像は、Entityに追加されない
                    if ($ProductImage instanceof ProductImage) {
                        $Product->removeProductImage($ProductImage);
                        $this->entityManager->remove($ProductImage);
                    }
                    $this->entityManager->persist($Product);

                    // 削除
                    $fs = new Filesystem();
                    $fs->remove($this->eccubeConfig['eccube_save_image_dir'] . '/' . $delete_image);
                }
                $this->entityManager->persist($Product);
                $this->entityManager->flush();

                $sortNos = $request->get('sort_no_images');
                if ($sortNos) {
                    foreach ($sortNos as $sortNo) {
                        list($filename, $sortNo_val) = explode('//', $sortNo);
                        $ProductImage = $this->productImageRepository
                            ->findOneBy([
                                'file_name' => $filename,
                                'Product' => $Product,
                            ]);
                        $ProductImage->setSortNo($sortNo_val);
                        $this->entityManager->persist($ProductImage);
                    }
                }
                $this->entityManager->flush();

                // 商品タグの登録
                // 商品タグを一度クリア
                $ProductTags = $Product->getProductTag();
                foreach ($ProductTags as $ProductTag) {
                    $Product->removeProductTag($ProductTag);
                    $this->entityManager->remove($ProductTag);
                }

                // 商品タグの登録
                $Tags = $form->get('Tag')->getData();
                foreach ($Tags as $Tag) {
                    $ProductTag = new ProductTag();
                    $ProductTag
                        ->setProduct($Product)
                        ->setTag($Tag);
                    $Product->addProductTag($ProductTag);
                    $this->entityManager->persist($ProductTag);
                }

                $Product->setUpdateDate(new \DateTime());
                $this->entityManager->flush();

                log_info('商品登録完了', [$id]);

                $event = new EventArgs(
                    [
                        'form' => $form,
                        'Product' => $Product,
                    ],
                    $request
                );
                $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE, $event);

                $this->addSuccess('admin.common.save_complete', 'admin');

                if ($returnLink = $form->get('return_link')->getData()) {
                    try {
                        // $returnLinkはpathの形式で渡される. pathが存在するかをルータでチェックする.
                        $pattern = '/^' . preg_quote($request->getBasePath(), '/') . '/';
                        $returnLink = preg_replace($pattern, '', $returnLink);
                        $result = $router->match($returnLink);
                        // パラメータのみ抽出
                        $params = array_filter($result, function ($key) {
                            return 0 !== \strpos($key, '_');
                        }, ARRAY_FILTER_USE_KEY);

                        // pathからurlを再構築してリダイレクト.
                        return $this->redirectToRoute($result['_route'], $params);
                    } catch (\Exception $e) {
                        // マッチしない場合はログ出力してスキップ.
                        log_warning('URLの形式が不正です。');
                    }
                }

                $cacheUtil->clearDoctrineCache();

                return $this->redirectToRoute('admin_product_product_edit', ['id' => $Product->getId()]);
            }
        }

        // 検索結果の保持
        $builder = $this->formFactory
            ->createBuilder(SearchProductType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_EDIT_SEARCH, $event);

        $searchForm = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
        }

        // Get Tags
        $TagsList = $this->tagRepository->getList();

        // ツリー表示のため、ルートからのカテゴリを取得
        $TopCategories = $this->categoryRepository->getList(null);
        $ChoicedCategoryIds = array_map(function ($Category) {
            return $Category->getId();
        }, $form->get('Category')->getData());

        return [
            'Product' => $Product,
            'Tags' => $Tags,
            'TagsList' => $TagsList,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'has_class' => $has_class,
            'id' => $id,
            'TopCategories' => $TopCategories,
            'ChoicedCategoryIds' => $ChoicedCategoryIds,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/product/product/{id}/delete", requirements={"id" = "\d+"}, name="admin_product_product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id = null, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();
        $session = $request->getSession();
        $page_no = intval($session->get('eccube.admin.product.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;
        $message = null;
        $success = false;

        if (!is_null($id)) {
            /* @var $Product \Eccube\Entity\Product */
            $Product = $this->productRepository->find($id);
            if (!$Product) {
                if ($request->isXmlHttpRequest()) {
                    $message = trans('admin.common.delete_error_already_deleted');

                    return $this->json(['success' => $success, 'message' => $message]);
                } else {
                    $this->deleteMessage();
                    $rUrl = $this->generateUrl('admin_product_page', ['page_no' => $page_no]) . '?resume=' . Constant::ENABLED;

                    return $this->redirect($rUrl);
                }
            }

            if ($Product instanceof Product) {
                log_info('商品削除開始', [$id]);

                $deleteImages = $Product->getProductImage();
                $ProductClasses = $Product->getProductClasses();

                try {
                    $this->productRepository->delete($Product);
                    $this->entityManager->flush();

                    $event = new EventArgs(
                        [
                            'Product' => $Product,
                            'ProductClass' => $ProductClasses,
                            'deleteImages' => $deleteImages,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_DELETE_COMPLETE, $event);
                    $deleteImages = $event->getArgument('deleteImages');

                    // 画像ファイルの削除(commit後に削除させる)
                    foreach ($deleteImages as $deleteImage) {
                        try {
                            $fs = new Filesystem();
                            $fs->remove($this->eccubeConfig['eccube_save_image_dir'] . '/' . $deleteImage);
                        } catch (\Exception $e) {
                            // エラーが発生しても無視する
                        }
                    }

                    log_info('商品削除完了', [$id]);

                    $success = true;
                    $message = trans('admin.common.delete_complete');

                    $cacheUtil->clearDoctrineCache();
                } catch (ForeignKeyConstraintViolationException $e) {
                    log_info('商品削除エラー', [$id]);
                    $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $Product->getName()]);
                }
            } else {
                log_info('商品削除エラー', [$id]);
                $message = trans('admin.common.delete_error');
            }
        } else {
            log_info('商品削除エラー', [$id]);
            $message = trans('admin.common.delete_error');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => $success, 'message' => $message]);
        } else {
            if ($success) {
                $this->addSuccess($message, 'admin');
            } else {
                $this->addError($message, 'admin');
            }

            $rUrl = $this->generateUrl('admin_product_page', ['page_no' => $page_no]) . '?resume=' . Constant::ENABLED;

            return $this->redirect($rUrl);
        }
    }

    /**
     * @Route("/%eccube_admin_route%/product/product/{id}/copy", requirements={"id" = "\d+"}, name="admin_product_product_copy", methods={"POST"})
     */
    public function copy(Request $request, $id = null)
    {
        $this->isTokenValid();

        if (!is_null($id)) {
            $Product = $this->productRepository->find($id);
            if ($Product instanceof Product) {
                $CopyProduct = clone $Product;
                $CopyProduct->copy();
                $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_HIDE);
                $CopyProduct->setStatus($ProductStatus);

                $CopyProductCategories = $CopyProduct->getProductCategories();
                foreach ($CopyProductCategories as $Category) {
                    $this->entityManager->persist($Category);
                }

                // 規格あり商品の場合は, デフォルトの商品規格を取得し登録する.
                if ($CopyProduct->hasProductClass()) {
                    $dummyClass = $this->productClassRepository->findOneBy([
                        'visible' => false,
                        'ClassCategory1' => null,
                        'ClassCategory2' => null,
                        'Product' => $Product,
                    ]);
                    $dummyClass = clone $dummyClass;
                    $dummyClass->setProduct($CopyProduct);
                    $CopyProduct->addProductClass($dummyClass);
                }

                $CopyProductClasses = $CopyProduct->getProductClasses();
                foreach ($CopyProductClasses as $Class) {
                    $Stock = $Class->getProductStock();
                    $CopyStock = clone $Stock;
                    $CopyStock->setProductClass($Class);
                    $this->entityManager->persist($CopyStock);

                    $TaxRule = $Class->getTaxRule();
                    if ($TaxRule) {
                        $CopyTaxRule = clone $TaxRule;
                        $CopyTaxRule->setProductClass($Class);
                        $CopyTaxRule->setProduct($CopyProduct);
                        $this->entityManager->persist($CopyTaxRule);
                    }
                    $this->entityManager->persist($Class);
                }
                $Images = $CopyProduct->getProductImage();
                foreach ($Images as $Image) {
                    // 画像ファイルを新規作成
                    $extension = pathinfo($Image->getFileName(), PATHINFO_EXTENSION);
                    $filename = date('mdHis') . uniqid('_') . '.' . $extension;
                    try {
                        $fs = new Filesystem();
                        $fs->copy($this->eccubeConfig['eccube_save_image_dir'] . '/' . $Image->getFileName(), $this->eccubeConfig['eccube_save_image_dir'] . '/' . $filename);
                    } catch (\Exception $e) {
                        // エラーが発生しても無視する
                    }
                    $Image->setFileName($filename);

                    $this->entityManager->persist($Image);
                }
                $Tags = $CopyProduct->getProductTag();
                foreach ($Tags as $Tag) {
                    $this->entityManager->persist($Tag);
                }

                $this->entityManager->persist($CopyProduct);

                $this->entityManager->flush();

                $event = new EventArgs(
                    [
                        'Product' => $Product,
                        'CopyProduct' => $CopyProduct,
                        'CopyProductCategories' => $CopyProductCategories,
                        'CopyProductClasses' => $CopyProductClasses,
                        'images' => $Images,
                        'Tags' => $Tags,
                    ],
                    $request
                );
                $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_COPY_COMPLETE, $event);

                $this->addSuccess('admin.product.copy_complete', 'admin');

                return $this->redirectToRoute('admin_product_product_edit', ['id' => $CopyProduct->getId()]);
            } else {
                $this->addError('admin.product.copy_error', 'admin');
            }
        } else {
            $msg = trans('admin.product.copy_error');
            $this->addError($msg, 'admin');
        }

        return $this->redirectToRoute('admin_product');
    }

    /**
     * @Route("/%eccube_admin_route%/product/product/{id}/display", requirements={"id" = "\d+"}, name="admin_product_product_display")
     */
    public function display(Request $request, $id = null)
    {
        $event = new EventArgs(
            [],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_DISPLAY_COMPLETE, $event);

        if (!is_null($id)) {
            return $this->redirectToRoute('product_detail', ['id' => $id, 'admin' => '1']);
        }

        return $this->redirectToRoute('admin_product');
    }

    /**
     * 商品CSVの出力.
     *
     * @Route("/%eccube_admin_route%/product/export", name="admin_product_export")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function export(Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // CSV種別を元に初期化.
            $this->csvExportService->initCsvType(CsvType::CSV_TYPE_PRODUCT);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 商品データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getProductQueryBuilder($request);

            // Get stock status
            $isOutOfStock = 0;
            $session = $request->getSession();
            if ($session->has('eccube.admin.product.search')) {
                $searchData = $session->get('eccube.admin.product.search', []);
                if (isset($searchData['stock_status']) && $searchData['stock_status'] === 0) {
                    $isOutOfStock = 1;
                }
            }

            // joinする場合はiterateが使えないため, select句をdistinctする.
            // http://qiita.com/suin/items/2b1e98105fa3ef89beb7
            // distinctのmysqlとpgsqlの挙動をあわせる.
            // http://uedatakeshi.blogspot.jp/2010/04/distinct-oeder-by-postgresmysql.html
            $qb->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->orderBy('p.update_date', 'DESC');

            if ($isOutOfStock) {
                $qb->select('p, pc')
                    ->distinct();
            } else {
                $qb->select('p')
                    ->distinct();
            }
            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);

            $this->csvExportService->exportData(function ($entity, CsvExportService $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();

                /** @var $Product \Eccube\Entity\Product */
                $Product = $entity;

                /** @var $ProductClasses \Eccube\Entity\ProductClass[] */
                $ProductClasses = $Product->getProductClasses();

                foreach ($ProductClasses as $ProductClass) {
                    $ExportCsvRow = new ExportCsvRow();

                    // CSV出力項目と合致するデータを取得.
                    foreach ($Csvs as $Csv) {
                        // 商品データを検索.
                        $ExportCsvRow->setData($csvService->getData($Csv, $Product));
                        if ($ExportCsvRow->isDataNull()) {
                            // 商品規格情報を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $ProductClass));
                        }

                        $event = new EventArgs(
                            [
                                'csvService' => $csvService,
                                'Csv' => $Csv,
                                'ProductClass' => $ProductClass,
                                'ExportCsvRow' => $ExportCsvRow,
                            ],
                            $request
                        );
                        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_CSV_EXPORT, $event);

                        $ExportCsvRow->pushData();
                    }

                    // $row[] = number_format(memory_get_usage(true));
                    // 出力.
                    $csvService->fputcsv($ExportCsvRow->getRow());
                }
            });
        });

        $now = new \DateTime();
        $filename = 'product_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->send();

        log_info('商品CSV出力ファイル名', [$filename]);

        return $response;
    }

    /**
     * ProductCategory作成
     *
     * @param \Eccube\Entity\Product $Product
     * @param \Eccube\Entity\Category $Category
     * @param integer $count
     *
     * @return \Eccube\Entity\ProductCategory
     */
    private function createProductCategory($Product, $Category, $count)
    {
        $ProductCategory = new ProductCategory();
        $ProductCategory->setProduct($Product);
        $ProductCategory->setProductId($Product->getId());
        $ProductCategory->setCategory($Category);
        $ProductCategory->setCategoryId($Category->getId());

        return $ProductCategory;
    }

    /**
     * Bulk public action
     *
     * @Route("/%eccube_admin_route%/product/bulk/product-status/{id}", requirements={"id" = "\d+"}, name="admin_product_bulk_product_status", methods={"POST"})
     *
     * @param Request $request
     * @param ProductStatus $ProductStatus
     *
     * @return RedirectResponse
     */
    public function bulkProductStatus(Request $request, ProductStatus $ProductStatus, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();

        /** @var Product[] $Products */
        $Products = $this->productRepository->findBy(['id' => $request->get('ids')]);
        $count = 0;
        foreach ($Products as $Product) {
            try {
                $Product->setStatus($ProductStatus);
                $this->productRepository->save($Product);
                $count++;
            } catch (\Exception $e) {
                $this->addError($e->getMessage(), 'admin');
            }
        }
        try {
            if ($count) {
                $this->entityManager->flush();
                $msg = $this->translator->trans('admin.product.bulk_change_status_complete', [
                    '%count%' => $count,
                    '%status%' => $ProductStatus->getName(),
                ]);
                $this->addSuccess($msg, 'admin');
                $cacheUtil->clearDoctrineCache();
            }
        } catch (\Exception $e) {
            $this->addError($e->getMessage(), 'admin');
        }

        return $this->redirectToRoute('admin_product', ['resume' => Constant::ENABLED]);
    }

    /**
     * 仕入先管理
     *
     * @Route("/%eccube_admin_route%/product/supplier", name="admin_product_supplier")
     * @Template("@admin/Product/supplier.twig")
     */
    public function supplier(Request $request, PaginatorInterface $paginator)
    {
        $idDestroy = $request->get('id-destroy');
        if ($idDestroy) {
            $supplier = $this->supplierRepository->find($request->get('id-destroy'));
            $issetProduct = $this->productClassRepository->findBy(['supplier_code' => $supplier->getSupplierCode()]);
            if (!$issetProduct) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($supplier);
                $entityManager->flush();
            }
            return $this->redirectToRoute('admin_product_supplier');
        }
        $supplierNew = new Supplier();

        $form = $this->createForm(SupplierType::class, $supplierNew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supplierNew);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_supplier');
        }

        $suppliers = $this->supplierRepository->findAll();
        $formUpdate = [];
        foreach ($suppliers as $supplier) {
            $uniqueFormName = 'Form' . $supplier->getId();
            $formHandle = $this->get('form.factory')->createNamed($uniqueFormName, SupplierType::class, $supplier);
            $formUpdate[$uniqueFormName] = $formHandle;
            $supplier->is_destroy = (bool)$this->productClassRepository->findBy(['supplier_code' => $supplier->getSupplierCode()]);
        }
        $formUpdateView = [];
        foreach ($formUpdate as $formName => $formHandle) {
            if ($request->get('supplier-id')) {
                $supplier = $this->supplierRepository->find($request->get('supplier-id'));
                $formHandle->handleRequest($request);
                if ($formHandle->isSubmitted() && $formHandle->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($supplier);
                    $entityManager->flush();
                }
            }
            $formUpdateView[$formName] = $formHandle->createView();
        }

        $results = $paginator->paginate(
            $suppliers,
            $request->query->getInt('page') ?: 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('@admin/Product/supplier.twig', [
            'suppliers' => $results,
            'form' => $form->createView(),
            'form_update' => $formUpdateView
        ]);
    }

    /**
     * 廃棄管理画面
     *
     * @Route("/%eccube_admin_route%/product/waste", name="admin_product_waste")
     * @Template("@admin/Product/waste.twig")
     */
    public function waste(PaginatorInterface $paginator, Request $request)
    {
        if ($request->get('id_destroy') && $request->isMethod('POST')) {
            $waste = $this->stockWasteRepository->find($request->get('id_destroy'));
            $productClass = $waste->getProductClass();
            $productClass->setStock($productClass->getStock() + $waste->getWasteUnit());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($productClass);
            $entityManager->remove($waste);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_waste');
        }
        $dateFrom = [
            'yearFrom' => $request->get('year_from'),
            'monthFrom' => $request->get('month_from'),
        ];

        $dateTo = [
            'yearTo' => $request->get('year_to'),
            'monthTo' => $request->get('month_to'),
        ];
        $result = $this->getListWasteQueryService->search($dateFrom, $dateTo);

        $wastes = $paginator->paginate(
            $result,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return [
            'wastes' => $wastes
        ];
    }


    /**
     * 廃棄情報登録画面
     *
     * @Route("/%eccube_admin_route%/product/waste/{id}", requirements={"id" = "\d+"}, name="admin_product_waste_regist")
     * @Template("@admin/Product/waste_regist.twig")
     */
    public function waste_regist(Request $request)
    {
        $productClassId = $request->get('id');
        $productClass = $this->productClassRepository->find($productClassId);
        if (!$productClass) {
            throw new NotFoundHttpException();
        }
        $product = null;

        $stockWaste = new StockWaste();
        $form = $this->createForm(StockWasteType::class, $stockWaste);
        $form->handleRequest($request);

        if ($productClass) {
            $product = $productClass->getProduct();

            if ($form->isSubmitted() && $form->isValid() && $product) {
                $stockProductClass = $request->get('stock_waste')['waste_unit'];
                $stockWaste->setProduct($product)
                           ->setProductClass($productClass);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($stockWaste);
                $entityManager->flush();
                $this->productClassRepository->decrementStock($productClass, $stockProductClass);

                return $this->redirectToRoute('admin_product_waste');
            }
        }

        return [
            'product' => $product,
            'form' => $form->createView()
        ];
    }

    /**
     * 入荷情報登録画面
     *
     * @Route("/%eccube_admin_route%/product/instock", name="admin_product_instock_list")
     * @Template("@admin/Product/instock_list.twig")
     */
    public function instock_list(PaginatorInterface $paginator, Request $request)
    {
        $instockDate = new InstockScheduleHeader();
        $supplier = [];
        $instocks = null;
        if ($request->getMethod('get')) {
            $orderDate = [
                'orderDateYear' => $request->get('order_date_year'),
                'orderDateMonth' => $request->get('order_date_month'),
                'orderDateDay' => $request->get('order_date_day')
            ];

            $scheduleDate = [
                'scheduleDateYear' => $request->get('arrival_date_schedule_year'),
                'scheduleDateMonth' => $request->get('arrival_date_schedule_month'),
                'scheduleDateDay' => $request->get('arrival_date_schedule_day')
            ];
            $instocks = $this->listInstockQueryService->search($orderDate, $scheduleDate);
        }
        if ($instocks) {
            foreach ($instocks as $instock) {
                $suppliers = $this->supplierRepository->findOneBy(['supplier_code' => $instock->getSupplierCode()]);
                $supplier[$instock->getSupplierCode()] = $suppliers->getSupplierName();
            }
        }
        $count = count($instocks);
        $instocks = $paginator->paginate(
            $instocks,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );

        return [
            'instocks' => $instocks,
            'supplier' => $supplier,
            'count' => $count,
        ];
    }

    /**
     * Delete instock header and schedule by id
     *
     * @Route("/%eccube_admin_route%/product/instock/delete", name="admin_product_instock_delete")
     */
    public function deleteInstock(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        if ($request->get('id')) {
            $instockHeader = $this->instockScheduleHeaderRepository->find($request->get('id'));
            $instocks = $this->instockScheduleRepository->findBy(['InstockHeader' => $request->get('id')]);
            if ($instocks) {
                foreach ($instocks as $instock) {
                    $entityManager->remove($instock);
                }
                $entityManager->flush();
            }
            $entityManager->remove($instockHeader);
        }
        $entityManager->flush();
        return new JsonResponse('success');
    }

    /**
     * 入荷情報登録画面
     *
     * @Route("/%eccube_admin_route%/product/instock/new", name="admin_product_instock_registration_new")
     * @Route("/%eccube_admin_route%/product/instock/edit/{id}", name="admin_product_instock_registration_edit")
     * @Template("@admin/Product/instock_edit.twig")
     */
    public function instock_registration(Request $request, $id = null)
    {
        $TargetInstock = null;
        $totalPrice = 0;
        $subTotalPrices = [];

        if ($id) {
            $TargetInstock = $this->instockScheduleHeaderRepository->find($id);
            if (!$TargetInstock) {
                throw new NotFoundHttpException();
            }
            // 編集前の受注情報を保持
            $OriginItems = new ArrayCollection();
            foreach ($TargetInstock->getInstockSchedule() as $schedule) {
                $item = new OrderItem;
                $item->setId($schedule->getId());
                $item->setOrderItemType($this->orderItemTypeRepository->find(1));
                $item->setQuantity($schedule->getArrivalQuantitySchedule());
                $item->setTaxRate($schedule->getArrivalBoxSchedule());
                $item->setPrice($schedule->getProductClass()->getItemCost());
                $item->setProduct($schedule->getProductClass()->getProduct());
                $item->setProductClass($schedule->getProductClass());
                $item->setProductName($schedule->getProductClass()->getProduct()->getName());
                $item->setProductCode($schedule->getProductClass()->getCode());
                if ($schedule->getProductClass()->getClassCategory1()) {
                    $item->setClassName1('フレーバー');
                    $item->setClassCategoryName1($schedule->getProductClass()->getClassCategory1()->getName());
                }
                if ($schedule->getProductClass()->getClassCategory2()) {
                    $item->setClassName2('サイズ');
                    $item->setClassCategoryName2($schedule->getProductClass()->getClassCategory2()->getName());
                }
                $OriginItems->add($item);
            }
            $TargetInstock->setInstockSchedule();
            foreach ($OriginItems as $key => $item) {
                $TargetInstock->addInstockSchedule($item);
                $subTotalPrices[$key] = $this->calcPrice($item);
            }
            $totalPrice = array_sum($subTotalPrices);
        } else {
            // 空のエンティティを作成.
            $TargetInstock = new InstockScheduleHeader();
        }
        $builder = $this->formFactory->createBuilder(
            InstockScheduleHeaderType::class,
            $TargetInstock,
            [
                'isEdit' => !!$id
            ]
        );
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form['InstockSchedule']->isValid()) {
            $subTotalPrices = [];
            $items = $form['InstockSchedule']->getData();
            foreach ($items as $key => $item) {
                $subTotalPrices[$key] = $this->calcPrice($item);
            }
            $totalPrice = array_sum($subTotalPrices);
            switch ($request->get('mode')) {
                case 'register':
                    log_info('受注登録開始', [$TargetInstock->getId()]);
                    if ($form->isValid()) {
                        $TargetInstock->setInstockSchedule(); // clear temp orderitem data
                        $this->entityManager->persist($TargetInstock);
                        $this->entityManager->flush();

                        $idScheduleDb = [];
                        $idScheduleReq = [];
                        foreach ($this->instockScheduleRepository->findBy(['InstockHeader' => $TargetInstock]) as $scheduleHeader) {
                            array_push($idScheduleDb, $scheduleHeader->getId());
                        }
                        foreach ($items as $key => $item) {
                            array_push($idScheduleReq, $item['id']);
                            if ($item['id']) {
                                $InstockSchedule = $this->instockScheduleRepository->find($item['id']);
                                $InstockSchedule->setJanCode($item->getProductCode())
                                    ->setPurchasePrice($subTotalPrices[$key])
                                    ->setArrivalQuantitySchedule($item->getQuantity())
                                    ->setArrivalBoxSchedule($item->getTaxRate())
                                    ->setProductClass($item->getProductClass());
                            } else {
                                $InstockSchedule = (new InstockSchedule())
                                    ->setInstockHeader($TargetInstock)
                                    ->setWarehouseCode('00001')
                                    ->setItemCode01('')
                                    ->setItemCode02('')
                                    ->setJanCode($item->getProductCode())
                                    ->setPurchasePrice($subTotalPrices[$key])
                                    ->setArrivalQuantitySchedule($item->getQuantity())
                                    ->setArrivalBoxSchedule($item->getTaxRate())
                                    ->setProductClass($item->getProductClass());
                            }
                            $this->entityManager->persist($InstockSchedule);
                        }
                        foreach ($idScheduleDb as $item) {
                            if (!in_array($item, $idScheduleReq)) {
                                $scheduleDel = $this->instockScheduleRepository->find($item);
                                $this->entityManager->remove($scheduleDel);
                            }
                        }
                        $this->entityManager->flush();

                        $this->addSuccess('admin.common.save_complete', 'admin');
                        log_info('受注登録完了', [$TargetInstock->getId()]);
                        return $this->redirectToRoute($id ? 'admin_product_instock_list' : 'admin_product_instock_registration_new');
                    }
                    break;
                default:
                    break;
            }
        }
        // 商品検索フォーム
        $builder = $this->formFactory->createBuilder(SearchProductType::class);
        $searchProductModalForm = $builder->getForm();

        return [
            'form' => $form->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Order' => $TargetInstock,
            'id' => $id,
            'totalPrice' => $totalPrice,
            'subtotalPrices' => $subTotalPrices
        ];
    }

    /**
     * Calculate instock price
     *
     * @param $item
     * @return float|int
     */
    public function calcPrice($item)
    {
        $price = $item->getPrice();
        $quantity1 = $item->getQuantity();
        $quantity2 = $item->getTaxRate();
        $quantityBox = $item->getProduct()->getQuantityBox();
        $subTotalPrice = 0;
        if ($quantity1 == 0) {
            $subTotalPrice = $price * $quantity2 * $quantityBox;
        } elseif ($quantity2 == 0) {
            $subTotalPrice = $price * $quantity1;
        } else {
            $subTotalPrice = $price * $quantity1 + $price * $quantity2 * $quantityBox;
        }
        return $subTotalPrice;
    }

    /**
     * @Route("/%eccube_admin_route%/product/waste/search/product", name="admin_waste_search_product")
     * @Route("/%eccube_admin_route%/product/waste/search/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_waste_search_product_page")
     * @Template("@admin/Product/waste_search_product.twig")
     */
    public function searchProduct(Request $request, Paginator $paginator, $page_no = null)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            log_debug('waste search product start.');
            $page_count = $this->eccubeConfig['eccube_default_page_count'];
            $session = $this->session;

            if ('POST' === $request->getMethod()) {
                $page_no = 1;

                $searchData = [
                    'keyword' => $request->get('keyword'),
                ];

                $session->set('eccube.admin.waste.product.search', $searchData);
                $session->set('eccube.admin.waste.product.search.page_no', $page_no);
            } else {
                $searchData = (array)$session->get('eccube.admin.waste.product.search');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.waste.product.search.page_no'));
                } else {
                    $session->set('eccube.admin.waste.product.search.page_no', $page_no);
                }
            }

            $qb = $this->productClassRepository->getQueryBuilderBySearchDataForAdmin($searchData);

            /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
            $pagination = $paginator->paginate(
                $qb,
                $page_no,
                $page_count,
                ['wrap-queries' => true]
            );

            /** @var $Products \Eccube\Entity\Product[] */
            $Products = $pagination->getItems();

            if (empty($Products)) {
                log_debug('waste search product not found.');
            }

            return [
                'pagination' => $pagination
            ];
        }
        return [];
    }
}
