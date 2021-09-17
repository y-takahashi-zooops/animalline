<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ShippingSchedule;
use Customize\Entity\ShippingScheduleHeader;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\ShippingScheduleHeaderRepository;
use Customize\Repository\ShippingScheduleRepository;
use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductRepository;
use Exception;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ShippingRepository;
use Eccube\Repository\OrderItemRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 */
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

        $dir = 'var/tmp/wms/shipping_schedule';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, 'R');
        }

        $now = Carbon::now();

        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => 4], ['sync_date' => 'DESC']);

        $qb = $this->shippingScheduleRepository->createQueryBuilder('ss');
        $qb->select(
            'IDENTITY(ssh.Shipping) as shippingInstructionNo',
            'ssh.shipping_date_schedule',
            'ssh.arrival_date_schedule',
            'ssh.arrival_time_code_schedule',
            'ssh.arrival_time_code_schedule',
            'ss.warehouse_code',
            'ss.item_code_01',
            'ss.item_code_02',
            'ss.jan_code',
            'ss.quantity',
            'ss.standerd_price',
            'ss.selling_price',
            'ssh.customer_name',
            'ssh.customer_zip',
            'ssh.customer_address',
            'ssh.customer_tel',
            'ssh.total_price',
            'ssh.discounted_price',
            'ssh.tax_price',
            'ssh.postage_price',
            'ssh.total_weight',
            'ssh.shipping_units',
            'ss.item_code_01',
            'IDENTITY(ssh.Shipping) as slipOutputOrder'
        )
            ->innerJoin('ss.ShippingScheduleHeader', 'ssh')
            ->leftJoin('ssh.Shipping', 's')
            ->where('s.update_date <= :to')
            ->setParameters(['to' => $now]);
        if ($syncInfo) $qb = $qb->andWhere('s.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $qb = $qb->orderBy('s.update_date', 'DESC');
        $records = $qb->getQuery()->getArrayResult();

        $query = $this->shippingRepository->createQueryBuilder('s');
        $query->where('s.update_date <= :to')
            ->setParameters(['to' => $now])
            ->andWhere('s.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $query = $query->getQuery()->getArrayResult();
        $arr = array_column($query, 'id');

        $filename = 'SHUSJI' . $now->format('Ymd_His') . '.csv';

        if ($records) {
            $isShipping = false;
            $wms = new WmsSyncInfo();
            $wms->setSyncAction(4)
                ->setSyncDate($now);
            try {
                $csvPath = $dir . '/' . $filename;
                $csvh = fopen($csvPath, 'w+') or die("Can't open file");
                $d = ','; // this is the default but i like to be explicit
                $e = '"'; // this is the default but i like to be explicit

                $result = [];

                foreach ($records as $record) {
                    if (in_array($record['shippingInstructionNo'], $arr)) {
                        $isShipping = true;
                        $wms->setSyncLog("alert");
                        continue;
                    }
                    $record['storeCode'] = null;
                    $record['saleCategory'] = '0';
                    $record['multiplicationRate'] = null;
                    $record['slipType'] = '0';
                    $record['sizeCode'] = '1';
                    $record['shippingCompanyCode'] = '000002';
                    $record['remarks'] = null;
                    $record['detailsRemarks'] = null;
                    $record['businessUnitName'] = null;
                    $record['areaName'] = null;
                    $record['destinationCode'] = null;
                    $record['destinationCode'] = null;
                    $record['areaCode'] = null;
                    $record['BBDATE'] = null;
                    $record['deliveryDestinationClassification'] = '1';
                    $record['coupon'] = null;
                    $record['paymentMethodClassification'] = '0';
                    $record['remark_2'] = null;
                    $record['remark_3'] = null;
                    $record['salesDestinationClassification'] = '01';
                    $record['commission'] = null;
                    $record['handlingFlightTypes'] = '000';
                    $record['destinationClassification'] = '1';
                    $sorted = [];
                    foreach ($fieldSorted as $value) {
                        array_push($sorted, $record[$value]);
                    }
                    array_push($result, $sorted);
                }
                foreach ($records as $record) {
                    $shipping = $this->shippingRepository->find($id=$record['shippingInstructionNo']);
                    $order = $shipping->getOrder();
                    $orderItem = $this->orderItemRepository->find($record['shippingInstructionNo']);

                    $shippingScheduleHeader = new ShippingScheduleHeader();
                    $shippingScheduleHeader->setShippingDateSchedule($now)
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

                    $shippingSchedule = new ShippingSchedule();

                    $shippingSchedule->setWarehouseCode($record['warehouse_code'])
                                     ->setItemCode01($orderItem->getProductClass()->getCode())
                                     ->setJanCode($orderItem->getProductClass()->getCode())
                                     ->setQuantity($orderItem->getQuantity())
                                     ->setStanderdPrice($orderItem->getPrice())
                                     ->setSellingPrice($orderItem->getPrice())
                                     ->setShippingScheduleHeader($shippingScheduleHeader)
                                     ->setOrderDetail($orderItem)
                                     ->setProductClass($orderItem->getProductClass());
                }
                foreach ($result as $item) {
                    fputcsv($csvh, $item, $d, $e);
                }
                fclose($csvh);
                $wms->setSyncResult($isShipping ? 2 : 1);
                echo 'Export succeeded.';
            } catch (Exception $e) {
                $wms->setSyncResult(3)
                ->setSyncLog($e->getMessage());
                echo 'Export failed.';
            }
            $em->persist($wms);
            $em->persist($shippingSchedule);
            $em->persist($shippingScheduleHeader);
            $em->flush();
        }
    }
}