<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Entity\ShippingSchedule;
use Customize\Entity\ShippingScheduleHeader;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\ShippingScheduleHeaderRepository;
use Customize\Repository\ShippingScheduleRepository;
use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Eccube\Repository\ShippingRepository;
use Eccube\Repository\OrderItemRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportRelease extends Command
{
    protected static $defaultName = 'eccube:customize:wms-shipping-schedule';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var ShippingScheduleHeaderRepository
     */
    protected $shippingScheduleHeaderRepository;

    /**
     * @var ShippingScheduleRepository
     */
    protected $shippingScheduleRepository;

    /**
     * @var WmsSyncInfoRepository
     */
    protected $wmsSyncInfoRepository;

    /**
     * @var ShippingRepository
     */
    protected $shippingRepository;

    /**
     * @var OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * ExportRelease constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository
     * @param ShippingScheduleRepository $shippingScheduleRepository
     * @param ShippingRepository $shippingRepository
     * @param OrderItemRepository $orderItemRepository
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        WmsSyncInfoRepository            $wmsSyncInfoRepository,
        ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository,
        ShippingScheduleRepository       $shippingScheduleRepository,
        ShippingRepository               $shippingRepository,
        OrderItemRepository              $orderItemRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->shippingScheduleHeaderRepository = $shippingScheduleHeaderRepository;
        $this->shippingScheduleRepository = $shippingScheduleRepository;
        $this->shippingRepository = $shippingRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    protected function configure()
    {
        $this->setDescription('Export csv release');
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
            'shippingInstructionNo', 'expectedShippingDate', 'expectedArrivalDate', 'arrivalTime', 'storeCode', 'warehouseCode',
            'saleCategory', 'multiplicationRate', 'slipType', 'productNumberCode', 'colorCode', 'sizeCode', 'JAN_code',
            'numberOfShippingInstructions', 'retailPrice', 'deliveryUnitPrice', 'shippingCompanyCode', 'remarks', 'deliveryName',
            'deliveryPostalCode', 'deliveryAddress', 'deliveryPhoneNumber', 'detailsRemarks', 'businessUnitName', 'areaName',
            'destinationCode', 'areaCode', 'BBDATE', 'deliveryDestinationClassification', 'totalProductPrice', 'discountAmount',
            'consumptionTax', 'postage', 'coupon', 'grossWeight', 'numberOfUnits', 'paymentMethodClassification', 'remark_2',
            'remark_3', 'salesDestinationClassification', 'partNumberCode_2', 'commission', 'handlingFlightTypes', 'destinationClassification',
            'slipOutputOrder'
        ];

        $dir = 'var/tmp/wms/shipping_schedule/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, 'R');
        }

        $now = Carbon::now();

        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => 4], ['sync_date' => 'DESC']);

        $query = $this->shippingRepository->createQueryBuilder('s');
        $query->where('s.update_date <= :to')
            ->setParameters(['to' => $now]);
        if ($syncInfo) $query = $query->andWhere('s.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $query = $query->getQuery()->getArrayResult();
        $arr = array_column($query, 'id');

        $filename = 'SHUSJI_' . $now->format('Ymd_His') . '.csv';
        $queryShipping = $this->shippingScheduleHeaderRepository->createQueryBuilder('ssh')
            ->innerJoin('ssh.Shipping', 's')
            ->where('s.update_date <= :to')
            ->setParameters(['to' => $now]);
        if ($syncInfo) $queryShipping = $queryShipping->andWhere('s.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $records = $queryShipping->getQuery()->getArrayResult();
        $arrId = array_column($queryShipping->select('s.id')->getQuery()->getArrayResult(), 'id');
        $arrDiff = array_diff($arr, $arrId);
        $wms = new WmsSyncInfo();
        $isShipping = false;
        if ($records) {
            $isShipping = true;
        }
        if ($arrDiff) {
            $queryNotInHeaders = $this->shippingRepository->createQueryBuilder('s');
            $queryNotInHeaders->andWhere('s.id in (:arr)')
                ->setParameter('arr', $arrDiff);
            $queryNotInHeaders = $queryNotInHeaders->getQuery()->getArrayResult();
            try {
                $csvPath = $dir . $filename;
                $csvh = fopen($csvPath, 'w+') or die("Can't open file");
                $d = ','; // this is the default but i like to be explicit
                $e = '"'; // this is the default but i like to be explicit

                foreach ($queryNotInHeaders as $queryNotInHeader) {
                    $shipping = $this->shippingRepository->find($queryNotInHeader['id']);
                    $order = $shipping->getOrder();
                    $orderItem = $this->orderItemRepository->findOneBy(['Shipping' => $shipping]);
                    if ($orderItem) {
                        $shippingScheduleHeader = new ShippingScheduleHeader();
                        $shippingScheduleHeader
                            ->setShipping($shipping)
                            ->setShippingDateSchedule($shipping->getShippingDate())
                            ->setArrivalDateSchedule($shipping->getShippingDeliveryDate())
                            ->setArrivalTimeCodeSchedule($shipping->getShippingDeliveryTime())
                            ->setCustomerName($shipping->getName01() . $shipping->getName02())
                            ->setCustomerZip($shipping->getPostalCode())
                            ->setCustomerAddress($shipping->getAddr01() . $shipping->getAddr02())
                            ->setCustomerTel($shipping->getPhoneNumber())
                            ->setTotalPrice($order->getSubTotal())
                            ->setDiscountedPrice($order->getDiscount())
                            ->setTaxPrice($order->getTax())
                            ->setPostagePrice($order->getDeliveryFeeTotal())
                            ->setTotalWeight($orderItem->getProductClass()
                                ->getProduct()->getItemWeight()
                            )
                            ->setShippingUnits(round(
                                (float)$orderItem->getProductClass()->getProduct()->getItemWeight() / 20))
                            ->setWmsSendDate($now)
                            ->setIsCancel(0);
                        $em->persist($shippingScheduleHeader);
                        $em->flush();

                        $shippingSchedule = new ShippingSchedule();

                        $shippingSchedule->setWarehouseCode('00000')
                            ->setItemCode01($orderItem->getProductClass()->getCode())
                            ->setJanCode($orderItem->getProductClass()->getCode())
                            ->setQuantity($orderItem->getQuantity())
                            ->setStanderdPrice($orderItem->getPrice())
                            ->setSellingPrice($orderItem->getPrice())
                            ->setShippingScheduleHeader($shippingScheduleHeader)
                            ->setOrderDetail($orderItem)
                            ->setProductClass($orderItem->getProductClass());

                        $em->persist($shippingSchedule);
                        $em->flush();
                        $sorted = [];
                        $queryCsv = $this->shippingScheduleRepository->createQueryBuilder('ss');
                        $queryCsv->select(
                            'IDENTITY(ssh.Shipping) as shippingInstructionNo',
                            'ssh.shipping_date_schedule as expectedShippingDate',
                            'ssh.arrival_date_schedule as expectedArrivalDate',
                            'ssh.arrival_time_code_schedule as arrivalTime',
                            'ss.warehouse_code as warehouseCode',
                            'ss.item_code_01 as productNumberCode',
                            'ss.item_code_02 as colorCode',
                            'ss.jan_code as JAN_code',
                            'ss.quantity as numberOfShippingInstructions',
                            'ss.standerd_price as retailPrice',
                            'ss.selling_price as deliveryUnitPrice',
                            'ssh.customer_name as deliveryName',
                            'ssh.customer_zip as deliveryPostalCode',
                            'ssh.customer_address as deliveryAddress',
                            'ssh.customer_tel as deliveryPhoneNumber',
                            'ssh.total_price as totalProductPrice',
                            'ssh.discounted_price as discountAmount',
                            'ssh.tax_price as consumptionTax',
                            'ssh.postage_price as postage',
                            'ssh.total_weight as grossWeight',
                            'ssh.shipping_units as numberOfUnits',
                            'ss.item_code_01 as partNumberCode_2',
                            'IDENTITY(ssh.Shipping) as slipOutputOrder'
                        )
                            ->innerJoin('ss.ShippingScheduleHeader', 'ssh')
                            ->leftJoin('ssh.Shipping', 's')
                            ->where('ssh.Shipping = :shipping')
                            ->andWhere('s.update_date <= :to')
                            ->setParameters(['to' => $now, 'shipping' => $shipping]);
                        if ($syncInfo) $queryCsv = $queryCsv->andWhere('s.update_date >= :from')
                            ->setParameter('from', $syncInfo->getSyncDate());
                        $queryCsv = $queryCsv->orderBy('s.update_date', 'DESC');
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
                            $recordCsv['detailsRemarks'] = null;
                            $recordCsv['businessUnitName'] = null;
                            $recordCsv['areaName'] = null;
                            $recordCsv['destinationCode'] = null;
                            $recordCsv['areaCode'] = null;
                            $recordCsv['BBDATE'] = null;
                            $recordCsv['deliveryDestinationClassification'] = '1';
                            $recordCsv['coupon'] = null;
                            $recordCsv['paymentMethodClassification'] = '0';
                            $recordCsv['remark_2'] = null;
                            $recordCsv['remark_3'] = null;
                            $recordCsv['salesDestinationClassification'] = '01';
                            $recordCsv['commission'] = null;
                            $recordCsv['handlingFlightTypes'] = '000';
                            $recordCsv['destinationClassification'] = '1';
                            $sorted = [];
                            foreach ($fieldSorted as $value) {
                                array_push($sorted, $recordCsv[$value]);
                            }
                            $sorted[1] = $sorted[1]->format('Y-m-d H:i:s');
                            $sorted[2] = $sorted[2]->format('Y-m-d H:i:s');
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
                $wms->setSyncResult(3)
                    ->setSyncDate($now)
                    ->setSyncLog($e->getMessage());
                echo $e->getMessage();
            }
        } else {
            $isShipping = true;
        }
        $wms->setSyncResult($isShipping ? 2 : 1)
            ->setSyncLog($isShipping ? "alert" : null)
            ->setSyncAction(4)
            ->setSyncDate($now);
        $em->persist($wms);
        $em->flush();
    }
}