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

use Carbon\Carbon;
use Customize\Entity\StockWaste;
use Customize\Repository\StockWasteReasonRepository;
use Customize\Repository\StockWasteRepository;
use Customize\Config\AnilineConf;
use Eccube\Entity\BaseInfo;
use Customize\Form\Type\Admin\StockWasteType;
use Eccube\Controller\AbstractController;
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
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Customize\Controller\Admin\Product\ProductController as BaseProductController;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;

class ProductWasteController extends BaseProductController
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
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductWasteController constructor.
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
     * @param OrderItemTypeRepository $orderItemTypeRepository
     * @param StockWasteRepository $stockWasteRepository
     * @param StockWasteReasonRepository $stockWasteReasonRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
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
        OrderItemTypeRepository         $orderItemTypeRepository,
        StockWasteRepository            $stockWasteRepository,
        StockWasteReasonRepository      $stockWasteReasonRepository,
        EccubeConfig $eccubeConfig
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
        $this->orderItemTypeRepository = $orderItemTypeRepository;
        $this->stockWasteRepository = $stockWasteRepository;
        $this->stockWasteReasonRepository = $stockWasteReasonRepository;
        $this->eccubeConfig = $eccubeConfig;
        
        // 親クラスのsetterメソッドを呼び出してプロパティを設定
        $this->setEntityManager($entityManager);
    }

    // public function index(Request $request, $page_no = null, PaginatorInterface $paginator)
    // {
    //     return $this->redirectToRoute('admin_product_waste');
    // }

    /**
     * 廃棄管理画面
     *
     * @Route("/%eccube_admin_route%/product/waste", name="admin_product_waste")
     * @Template("@admin/Product/waste.twig")
     * @throws Exception
     */
    public function waste(PaginatorInterface $paginator, Request $request)
    {
        if ($request->get('id_destroy') && $request->isMethod('POST')) {
            $waste = $this->stockWasteRepository->find($request->get('id_destroy'));
            $productClass = $waste->getProductClass();
            $productClass->setStock((int)$productClass->getStock() + $waste->getWasteUnit());
            $entityManager = $this->entityManager;
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
        $result = $this->stockWasteRepository->search($dateFrom, $dateTo);

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
    public function waste_regist(Request $request, ?int $id = null)
    {
        $productClassId = $request->get('id');
        $productClass = $this->productClassRepository->find($productClassId);
        if (!$productClass) {
            throw new NotFoundHttpException();
        }

        $stockWaste = new StockWaste();
        $form = $this->createForm(StockWasteType::class, $stockWaste);
        $form->handleRequest($request);


        $product = $productClass->getProduct();

        if ($form->isSubmitted() && $form->isValid() && $product) {
            $stockProductClass = $productClass->getStock();
            $stockUnit = $form['waste_unit']->getData() ? $form['waste_unit']->getData() : 0;

            if ($stockProductClass >= $stockUnit) {
                $stockWaste->setProduct($product)
                    ->setProductClass($productClass);
                $this->productClassRepository->decrementStock($productClass, $stockUnit);
                $productClass->setUpdateDate(Carbon::now());
                $entityManager = $this->entityManager;
                $entityManager->persist($stockWaste);
                $entityManager->persist($productClass);
                $entityManager->flush();

                $this->addSuccess('admin.common.save_complete', 'admin');
                return $this->redirectToRoute('admin_product_waste');
            }

            $this->addError('admin.common.save_error', 'admin');
            return $this->redirectToRoute('admin_product_waste_regist', ['id' => $productClass->getId()]);
        }


        return [
            'product' => $product,
            'form' => $form->createView()
        ];
    }

    /**
     * Search product for waster regist
     *
     * @Route("/%eccube_admin_route%/product/waste/search/product", name="admin_waste_search_product")
     * @Route("/%eccube_admin_route%/product/waste/search/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_waste_search_product_page")
     * @Template("@admin/Product/waste_search_product.twig")
     */
    public function searchProduct(Request $request, Paginator $paginator, $page_no = null): array
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
