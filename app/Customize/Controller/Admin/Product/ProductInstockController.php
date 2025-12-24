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
use Doctrine\Common\Collections\ArrayCollection;
use Customize\Form\Type\Admin\InstockScheduleHeaderType;
use Customize\Repository\InstockScheduleHeaderRepository;
use Customize\Repository\InstockScheduleRepository;
use Customize\Entity\InstockSchedule;
use Customize\Repository\SupplierRepository;
use Customize\Command\ExportInstockSchedule;
use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Admin\SearchProductType;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Repository\ProductStockRepository;
use Customize\Service\ProductStockService;
use Knp\Component\Pager\Paginator;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Form\Type\AddCartType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\CartItem;

use Psr\Log\LoggerInterface;

class ProductInstockController extends AbstractController
{
    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    /**
     * @var InstockScheduleHeaderRepository
     */
    protected $instockScheduleHeaderRepository;

    /**
     * @var InstockScheduleRepository
     */
    protected $instockScheduleRepository;

    /**
     * @var ExportInstockSchedule
     */
    protected $exportInstockSchedule;

    /**
     * @var ProductStockRepository
     */
    protected $productStockRepository;

    /**
     * @var ProductStockService
     */
    protected $productStockService;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductInstockController constructor.
     *
     * @param SupplierRepository $supplierRepository
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     * @param ExportInstockSchedule $exportInstockSchedule
     * @param ProductStockRepository $productStockRepository
     * @param ProductStockService $productStockService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SupplierRepository              $supplierRepository,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository,
        ExportInstockSchedule           $exportInstockSchedule,
        ProductStockRepository          $productStockRepository,
        ProductStockService          $productStockService,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        EccubeConfig $eccubeConfig
    ) {
        $this->supplierRepository = $supplierRepository;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
        $this->exportInstockSchedule = $exportInstockSchedule;
        $this->productStockRepository = $productStockRepository;
        $this->productStockService = $productStockService;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->eccubeConfig = $eccubeConfig;
        
        // 親クラスのsetterメソッドを呼び出してプロパティを設定
        $this->setEntityManager($entityManager);
        $this->setFormFactory($formFactory);
    }

    /**
     * 入荷情報登録画面
     *
     * @Route("/%eccube_admin_route%/product/instock", name="admin_product_instock_list")
     * @Template("@admin/Product/instock_list.twig")
     * @throws Exception
     */
    public function instock_list(PaginatorInterface $paginator, Request $request): array
    {
        $supplier = [];
        $instocks = null;
        if ($request->isMethod('GET')) {
            $orderDate = [
                'orderDateYear' => $request->get('order_date_year'),
                'orderDateMonth' => $request->get('order_date_month'),
                'orderDateDay' => $request->get('order_date_day')
            ];

            $orderDate2 = [
                'orderDateYear' => $request->get('order_date_year2'),
                'orderDateMonth' => $request->get('order_date_month2'),
                'orderDateDay' => $request->get('order_date_day2')
            ];

            $scheduleDate = [
                'scheduleDateYear' => $request->get('arrival_date_schedule_year'),
                'scheduleDateMonth' => $request->get('arrival_date_schedule_month'),
                'scheduleDateDay' => $request->get('arrival_date_schedule_day')
            ];

            $scheduleDate2 = [
                'scheduleDateYear' => $request->get('arrival_date_schedule_year2'),
                'scheduleDateMonth' => $request->get('arrival_date_schedule_month2'),
                'scheduleDateDay' => $request->get('arrival_date_schedule_day2')
            ];

            $instocks = $this->instockScheduleHeaderRepository->search($orderDate,$orderDate2,$scheduleDate,$scheduleDate2);
        }
        if ($instocks) {
            foreach ($instocks as $instock) {
                $suppliers = $this->supplierRepository->findOneBy(['supplier_code' => $instock->getSupplierCode()]);
                $supplier[$instock->getSupplierCode()] = $suppliers ? $suppliers->getSupplierName() : "_";
                $details[$instock->getId()] = $this->instockScheduleRepository->findBy(["InstockHeader" => $instock]);
            }
        }
        $count = count($instocks);
        $instocks = $paginator->paginate(
            $instocks,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );

        // 現在の年を取得
        $currentYear = date('Y');

        // 5年前の年
        $year5YearsAgo = $currentYear - 5;

        // 10年後の年
        $year10YearsLater = $currentYear + 10;

        // 年数を配列に格納
        $years = [];
        for ($year = $year5YearsAgo; $year <= $year10YearsLater; $year++) {
            $years[] = $year;
        }

        return [
            'instocks' => $instocks,
            'supplier' => $supplier,
            'count' => $count,
            'details' => $details,
            'years' => $years
        ];
    }

    /**
     * 入荷実績検索画面
     *
     * @Route("/%eccube_admin_route%/product/instock_search", name="admin_product_instock_search")
     * @Template("@admin/Product/instock_search.twig")
     * @throws Exception
     */
    public function instock_search(PaginatorInterface $paginator, Request $request): array
    {
        $supplier = [];
        $instocks = null;
        if ($request->isMethod('GET')) {
            $orderDate = [
                'orderDateYear' => $request->get('order_date_year'),
                'orderDateMonth' => $request->get('order_date_month'),
                'orderDateDay' => $request->get('order_date_day')
            ];

            $orderDate2 = [
                'orderDateYear' => $request->get('order_date_year2'),
                'orderDateMonth' => $request->get('order_date_month2'),
                'orderDateDay' => $request->get('order_date_day2')
            ];

            $scheduleDate = [
                'scheduleDateYear' => $request->get('arrival_date_schedule_year'),
                'scheduleDateMonth' => $request->get('arrival_date_schedule_month'),
                'scheduleDateDay' => $request->get('arrival_date_schedule_day')
            ];

            $scheduleDate2 = [
                'scheduleDateYear' => $request->get('arrival_date_schedule_year2'),
                'scheduleDateMonth' => $request->get('arrival_date_schedule_month2'),
                'scheduleDateDay' => $request->get('arrival_date_schedule_day2')
            ];

            $instocks = $this->instockScheduleRepository->search($orderDate,$orderDate2,$scheduleDate,$scheduleDate2);
        }
        
        $count = count($instocks);
        $instocks = $paginator->paginate(
            $instocks,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );

        // 現在の年を取得
        $currentYear = date('Y');

        // 5年前の年
        $year5YearsAgo = $currentYear - 5;

        // 10年後の年
        $year10YearsLater = $currentYear + 10;

        // 年数を配列に格納
        $years = [];
        for ($year = $year5YearsAgo; $year <= $year10YearsLater; $year++) {
            $years[] = $year;
        }

        return [
            'instocks' => $instocks,
            'count' => $count,
            'years' => $years
        ];
    }

    /**
     * WMSに入荷情報を送信する
     *
     * @Route("/%eccube_admin_route%/product/sendwms", name="admin_product_instock_send_wms")
     * 
     */
    public function instock_send_wms()
    {
        $this->exportInstockSchedule->exportInstock();

        return $this->redirectToRoute('admin_product_instock_list');
    }

    /**
     * Delete instock header and schedule by id
     *
     * @Route("/%eccube_admin_route%/product/instock/delete", name="admin_product_instock_delete")
     */
    public function deleteInstock(Request $request): JsonResponse
    {
        $entityManager = $this->entityManager;
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
        $totalPrice = 0;
        $subTotalPrices = [];
        $count = 0;

        if ($id) {
            $TargetInstock = $this->instockScheduleHeaderRepository->find($id);
            if (!$TargetInstock) {
                throw new NotFoundHttpException();
            }
            // 編集前の在庫情報を保持
            $OriginItems = new ArrayCollection();
            foreach ($TargetInstock->getInstockSchedule() as $schedule) {
                $count++;
                $item = new InstockSchedule;
                $item->setQuantity($schedule->getArrivalQuantitySchedule());
                $item->setTaxRate($schedule->getArrivalQuantity());
                $item->setPrice($schedule->getProductClass()->getItemCost());
                $item->setProductClass($schedule->getProductClass());
                $item->setProductName($schedule->getProductClass()->getProduct()->getName());
                $OriginItems->add($item);
            }
            $TargetInstock->setInstockSchedule();
            foreach ($OriginItems as $key => $item) {
                $TargetInstock->addInstockSchedule($item);
                $subTotalPrices[$key] = $item->getPrice() * $item->getQuantity();
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
                $subTotalPrices[$key] = $item->getPrice() * $item->getQuantity();
            }
            $totalPrice = array_sum($subTotalPrices);

            if ($request->get('mode') === 'register') {
                $this->logger->info('受注登録開始', [$TargetInstock->getId()]);
                if ($form->isValid()) {
                    $TargetInstock->setInstockSchedule();
                    if (!$id) {
                        $TargetInstock->setIsSendWms(0);
                        $TargetInstock->setIsCommit(0);
                    }
                    $this->entityManager->persist($TargetInstock);
                    $this->entityManager->flush();

                    $idScheduleDb = [];
                    $idScheduleReq = [];
                    foreach ($this->instockScheduleRepository->findBy(['InstockHeader' => $TargetInstock]) as $scheduleHeader) {
                        array_push($idScheduleDb, $scheduleHeader->getId());
                    }
                    foreach ($items as $key => $item) {
                        array_push($idScheduleReq, $item->getId());

                        $pc = $item->getProductClass();

                        if ($item->getId()) {
                            $InstockSchedule = $this->instockScheduleRepository->find($item['id']);
                            $InstockSchedule->setJanCode($pc->getJanCode())
                                ->setItemCode01($item->getProductCode())
                                ->setPurchasePrice($subTotalPrices[$key])
                                ->setArrivalQuantitySchedule($item->getQuantity())
                                ->setArrivalQuantity($item->getTaxRate())
                                ->setProductClass($pc);
                        } else {
                            $ITEM_CODE_02 = '9999';
                            $InstockSchedule = (new InstockSchedule)
                                ->setInstockHeader($TargetInstock)
                                ->setWarehouseCode($pc->getStockCode())
                                ->setItemCode01($pc->getCode())
                                ->setItemCode02($ITEM_CODE_02)
                                ->setJanCode($pc->getJanCode())
                                ->setPurchasePrice($subTotalPrices[$key])
                                ->setArrivalQuantitySchedule($item->getQuantity())
                                ->setArrivalQuantity($item->getTaxRate())
                                ->setProductClass($pc);
                        }
                        $this->entityManager->persist($InstockSchedule);

                        //在庫反映
                        if ($TargetInstock->getIsCommit() == 1) {
                            $this->productStockService->calculateStock($this->entityManager, $pc, $InstockSchedule->getArrivalQuantity());
                        }
                    }
                    foreach ($idScheduleDb as $item) {
                        if (in_array($item, $idScheduleReq)) continue;
                        $scheduleDel = $this->instockScheduleRepository->find($item);
                        $this->entityManager->remove($scheduleDel);
                    }
                    $this->entityManager->flush();

                    $this->addSuccess('admin.common.save_complete', 'admin');
                    $this->logger->info('受注登録完了', [$TargetInstock->getId()]);
                    return $this->redirectToRoute($id ? 'admin_product_instock_list' : 'admin_product_instock_registration_new');
                }
            }
        }

        // 商品検索フォーム
        $builder = $this->formFactory->createBuilder(SearchProductType::class);
        $searchProductModalForm = $builder->getForm();

        return [
            'form' => $form->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'totalPrice' => $totalPrice,
            'subtotalPrices' => $subTotalPrices,
            'count' => $count
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/instock/search/product", name="admin_instock_search_product")
     * @Route("/%eccube_admin_route%/instock/search/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_instock_search_product_page")
     * @Template("@admin/Product/search_product.twig")
     */
    public function searchProduct(Request $request, ?int $page_no = 1, PaginatorInterface $paginator)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            log_debug('search product start.');
            $page_count = $this->eccubeConfig['eccube_default_page_count'];
            $session = $this->session;

            if ('POST' === $request->getMethod()) {
                $page_no = 1;

                $searchData = [
                    'id' => $request->get('id'),
                ];

                if ($categoryId = $request->get('category_id')) {
                    $Category = $this->categoryRepository->find($categoryId);
                    $searchData['category_id'] = $Category;
                }

                $session->set('eccube.admin.instock.product.search', $searchData);
                $session->set('eccube.admin.instock.product.search.page_no', $page_no);
            } else {
                $searchData = (array) $session->get('eccube.admin.instock.product.search');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.instock.product.search.page_no'));
                } else {
                    $session->set('eccube.admin.instock.product.search.page_no', $page_no);
                }
            }

            $qb = $this->productRepository
                ->getQueryBuilderBySearchDataForAdmin($searchData);

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
                log_debug('search product not found.');
            }

            $forms = [];
            foreach ($Products as $Product) {
                /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
                $builder = $this->formFactory->createNamedBuilder('', AddCartType::class, null, [
                    'product' => $this->productRepository->findWithSortedClassCategories($Product->getId()),
                ]);
                $addCartForm = $builder->getForm();
                $forms[$Product->getId()] = $addCartForm->createView();
            }

            return [
                'forms' => $forms,
                'Products' => $Products,
                'pagination' => $pagination,
                'is_instock' => $request->get('is_instock') ?? 0,
                'reIndex' => $request->get('reIndex') ?? 0,
            ];
        }
    }
}
