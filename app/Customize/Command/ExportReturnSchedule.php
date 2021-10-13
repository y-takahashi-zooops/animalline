<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ReturnSchedule;
use Customize\Entity\ReturnScheduleHeader;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\ReturnScheduleHeaderRepository;
use Customize\Repository\ReturnScheduleRepository;
use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\OrderItemRepository;
use Eccube\Repository\OrderRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportReturnSchedule extends Command
{
    protected static $defaultName = 'eccube:customize:wms-return-schedule';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var ReturnScheduleHeaderRepository
     */
    protected $returnScheduleHeaderRepository;

    /**
     * @var ReturnScheduleRepository
     */
    protected $returnScheduleRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * @var WmsSyncInfoRepository
     */
    protected $wmsSyncInfoRepository;

    /**
     * ExportRelease constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ReturnScheduleHeaderRepository $returnScheduleHeaderRepository
     * @param ReturnScheduleRepository $returnScheduleRepository
     * @param OrderRepository $orderRepository
     * @param OrderItemRepository $orderItemRepository
     */
    public function __construct(
        EntityManagerInterface         $entityManager,
        WmsSyncInfoRepository          $wmsSyncInfoRepository,
        ReturnScheduleHeaderRepository $returnScheduleHeaderRepository,
        ReturnScheduleRepository       $returnScheduleRepository,
        OrderRepository                $orderRepository,
        OrderItemRepository            $orderItemRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->returnScheduleHeaderRepository = $returnScheduleHeaderRepository;
        $this->returnScheduleRepository = $returnScheduleRepository;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    protected function configure()
    {
        $this->setDescription('Export csv return');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;
        $fieldSorted = [
            'returnInstructionNo', 'expectedReturnDate', 'storeCode', 'warehouseCode',
            'saleCategory', 'multiplicationRate', 'slipType', 'productNumberCode', 'colorCode', 'sizeCode', 'JAN_code',
            'numberOfReturnInstructions', 'retailPrice', 'deliveryUnitPrice', 'shippingCompanyCode', 'remarks', 'deliveryName',
            'deliveryPostalCode', 'deliveryAddress', 'deliveryPhoneNumber', 'BBDATE', 'classification'
        ];

        $dir = 'var/tmp/wms/return_schedule/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, 'R');
        }

        $now = Carbon::now();

        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_RETURN], ['sync_date' => 'DESC']);

        $query = $this->orderRepository->createQueryBuilder('o');
        $query->where('o.update_date <= :to')
            ->andWhere('o.CustomerOrderStatus = ' . AnilineConf::ANILINE_RETURN_SCHEDULE)
            ->setParameters(['to' => $now]);
        if ($syncInfo) $query = $query->andWhere('o.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $query = $query->getQuery()->getArrayResult();
        $arr = array_column($query, 'id');

        $filename = 'HENPINOTEI_' . $now->format('Ymd_His') . '.csv';
        $queryReturn = $this->returnScheduleHeaderRepository->createQueryBuilder('rh')
            ->innerJoin('rh.Order', 'o')
            ->where('o.update_date <= :to')
            ->setParameters(['to' => $now]);
        if ($syncInfo) $queryReturn = $queryReturn->andWhere('o.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $records = $queryReturn->getQuery()->getArrayResult();
        $arrId = array_column($queryReturn->select('o.id')->getQuery()->getArrayResult(), 'id');
        $arrDiff = array_diff($arr, $arrId);
        $wms = new WmsSyncInfo();
        $isReturn = false;
        if ($records) {
            $isReturn = true;
        }
        if ($arrDiff) {
            $queryNotInHeaders = $this->orderRepository->createQueryBuilder('o');
            $queryNotInHeaders->andWhere('o.id in (:arr)')
                ->setParameter('arr', $arrDiff);
            $queryNotInHeaders = $queryNotInHeaders->getQuery()->getArrayResult();
            try {
                $csvPath = $dir . $filename;
                $csvh = fopen($csvPath, 'w+') or die("Can't open file");
                $d = ','; // this is the default but i like to be explicit
                $e = '"'; // this is the default but i like to be explicit

                foreach ($queryNotInHeaders as $queryNotInHeader) {
                    $order = $this->orderRepository->find($queryNotInHeader['id']);
                    $orderItem = $this->orderItemRepository->findOneBy(['Order' => $order]);
                    if ($orderItem) {
                        $returnScheduleHeader = new ReturnScheduleHeader();
                        $returnScheduleHeader->setReturnDateSchedule($order->getUpdateDate())
                            ->setCustomerName($order->getName01() . $order->getName02())
                            ->setCustomerZip($order->getPostalCode())
                            ->setCustomerAddress($order->getAddr01() . $order->getAddr02())
                            ->setCustomerTel($order->getPhoneNumber())
                            ->setOrder($order);
                        $em->persist($returnScheduleHeader);
                        $em->flush();
                        $returnSchedule = new ReturnSchedule();
                        $returnSchedule->setWarehouseCode($orderItem->getProductClass()->getStockCode())
                            ->setItemCode01($orderItem->getProductClass()->getCode())
                            ->setItemCode02('9999')
                            ->setJanCode($orderItem->getProductClass()->getCode())
                            ->setQuantitySchedule($orderItem->getQuantity())
                            ->setStanderdPrice($orderItem->getPrice())
                            ->setSellingPrice($orderItem->getPrice())
                            ->setReturnScheduleHeader($returnScheduleHeader)
                            ->setOrderItem($orderItem)
                            ->setProductClass($orderItem->getProductClass());

                        $em->persist($returnSchedule);
                        $em->flush();
                        $sorted = [];
                        $queryCsv = $this->returnScheduleRepository->createQueryBuilder('r');
                        $queryCsv->select(
                            'rh.id as returnInstructionNo',
                            'rh.return_date_schedule as expectedReturnDate',
                            'r.warehouse_code as warehouseCode',
                            'r.item_code_01 as productNumberCode',
                            'r.item_code_02 as colorCode',
                            'pc.jan_code as JAN_code',
                            'r.quantity_schedule as numberOfReturnInstructions',
                            'r.standerd_price as retailPrice',
                            'r.selling_price as deliveryUnitPrice',
                            'rh.customer_name as deliveryName',
                            'rh.customer_zip as deliveryPostalCode',
                            'rh.customer_address as deliveryAddress',
                            'rh.customer_tel as deliveryPhoneNumber',
                        )
                            ->innerJoin('r.ReturnScheduleHeader', 'rh')
                            ->leftJoin('rh.Order', 'o')
                            ->leftJoin('r.ProductClass', 'pc')
                            ->where('rh.Order = :order')
                            ->andWhere('o.update_date <= :to')
                            ->setParameters(['to' => $now, 'order' => $order]);
                        if ($syncInfo) $queryCsv = $queryCsv->andWhere('o.update_date >= :from')
                            ->setParameter('from', $syncInfo->getSyncDate());
                        $queryCsv = $queryCsv->orderBy('o.update_date', 'DESC');
                        $recordCsvs = $queryCsv->getQuery()->getArrayResult();
                        $result = [];
                        foreach ($recordCsvs as $recordCsv) {
                            $recordCsv['storeCode'] = null;
                            $recordCsv['saleCategory'] = '0';
                            $recordCsv['multiplicationRate'] = null;
                            $recordCsv['slipType'] = '0';
                            $recordCsv['sizeCode'] = '1';
                            $recordCsv['shippingCompanyCode'] = '000002';
                            $recordCsv['remarks'] = null;
                            $recordCsv['BBDATE'] = null;
                            $recordCsv['classification'] = '1';
                            $sorted = [];
                            foreach ($fieldSorted as $value) {
                                array_push($sorted, $recordCsv[$value]);
                            }
                            $sorted[1] = $sorted[1]->format('Y-m-d');
                            array_push($result, $sorted);
                        }

                        foreach ($result as $item) {
                            fputcsv($csvh, $item, $d, $e);
                        }
                    }
                }
                fclose($csvh);
                echo 'Export succeeded.' . "\n";
            } catch (Exception $e) {
                $wms = new WmsSyncInfo();
                $wms->setSyncResult(AnilineConf::ANILINE_WMS_RESULT_ERROR)
                    ->setSyncDate($now)
                    ->setSyncLog($e->getMessage());
                echo $e->getMessage();
            }
        } else {
            $isReturn = true;
        }
        $wms->setSyncResult($isReturn ? AnilineConf::ANILINE_WMS_RESULT_ANNOTATED : AnilineConf::ANILINE_WMS_RESULT_SUCCESS)
            ->setSyncLog($isReturn ? "alert" : null)
            ->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_RETURN)
            ->setSyncDate($now);
        $em->persist($wms);
        $em->flush();
    }
}
