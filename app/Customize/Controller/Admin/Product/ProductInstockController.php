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
use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Admin\SearchProductType;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Entity\OrderItem;
use Eccube\Repository\Master\OrderItemTypeRepository;

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
     * @var OrderItemTypeRepository
     */
    protected $orderItemTypeRepository;

    /**
     * ProductInstockController constructor.
     *
     * @param SupplierRepository $supplierRepository
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     * @param OrderItemTypeRepository $orderItemTypeRepository
     */
    public function __construct(
        SupplierRepository              $supplierRepository,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository,
        OrderItemTypeRepository         $orderItemTypeRepository
    ) {
        $this->supplierRepository = $supplierRepository;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
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

            $scheduleDate = [
                'scheduleDateYear' => $request->get('arrival_date_schedule_year'),
                'scheduleDateMonth' => $request->get('arrival_date_schedule_month'),
                'scheduleDateDay' => $request->get('arrival_date_schedule_day')
            ];
            $instocks = $this->instockScheduleHeaderRepository->search($orderDate, $scheduleDate);
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
            'count' => $count
        ];
    }

    /**
     * Delete instock header and schedule by id
     *
     * @Route("/%eccube_admin_route%/product/instock/delete", name="admin_product_instock_delete")
     */
    public function deleteInstock(Request $request): JsonResponse
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
        $totalPrice = 0;
        $subTotalPrices = [];
        $count = 0;

        if ($id) {
            $TargetInstock = $this->instockScheduleHeaderRepository->find($id);
            if (!$TargetInstock) {
                throw new NotFoundHttpException();
            }
            // 編集前の受注情報を保持
            $OriginItems = new ArrayCollection();
            foreach ($TargetInstock->getInstockSchedule() as $schedule) {
                $count++;
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
            'subtotalPrices' => $subTotalPrices,
            'count' => $count
        ];
    }

    /**
     * Calculate instock price
     *
     * @param $item
     * @return float|int
     */
    private function calcPrice($item)
    {
        $price = $item->getPrice();
        $quantity1 = $item->getQuantity();
        $quantity2 = $item->getTaxRate();
        $quantityBox = $item->getProduct()->getQuantityBox();
        if ($quantity1 == 0) {
            $subTotalPrice = $price * $quantity2 * $quantityBox;
        } elseif ($quantity2 == 0) {
            $subTotalPrice = $price * $quantity1;
        } else {
            $subTotalPrice = $price * $quantity1 + $price * $quantity2 * $quantityBox;
        }
        return $subTotalPrice;
    }
}
