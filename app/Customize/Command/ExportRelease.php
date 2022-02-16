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
use Customize\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Eccube\Repository\ShippingRepository;
use Eccube\Repository\OrderItemRepository;
use Eccube\Repository\OrderRepository;
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
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;


    /**
     * ExportRelease constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository
     * @param ShippingScheduleRepository $shippingScheduleRepository
     * @param ShippingRepository $shippingRepository
     * @param OrderItemRepository $orderItemRepository
     * @param OrderRepository $orderRepository
     * @param SupplierRepository $supplierRepository
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        WmsSyncInfoRepository            $wmsSyncInfoRepository,
        ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository,
        ShippingScheduleRepository       $shippingScheduleRepository,
        ShippingRepository               $shippingRepository,
        OrderItemRepository              $orderItemRepository,
        OrderRepository                  $orderRepository,
        SupplierRepository               $supplierRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->shippingScheduleHeaderRepository = $shippingScheduleHeaderRepository;
        $this->shippingScheduleRepository = $shippingScheduleRepository;
        $this->shippingRepository = $shippingRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->supplierRepository = $supplierRepository;
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

        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT], ['sync_date' => 'DESC']);

        $query = $this->orderRepository->createQueryBuilder('o');
        if ($syncInfo) $query = $query->andWhere('o.create_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $query = $query->andWhere('o.order_date is not null');

        $orders = $query->getQuery()->getResult();

        $filename = 'SHUSJI_' . $now->format('Ymd_His') . '.csv';
        $csvPath = $dir . $filename;
        
        try {
            //csvオープン
            $csvPath = $dir . $filename;
            $csvh = fopen($csvPath, 'w+') or die("Can't open file");

            $i = 1;
            foreach ($orders as $order) {
                var_dump($order->getId());
                
                $order_items = $this->orderItemRepository->findBy(array("Order" => $order));

                foreach ($order_items as $order_item) {
                    var_dump($order_item->getId());

                    $shipping = $order_item->getShipping();
                    $pc = $order_item->getProductClass();
                    
                    if($pc != null){
                        $product = $order_item->getProduct();
                        //$supplier =  $this->supplierRepository->find($pc->getSupplierCode());

                        // 着荷時刻を佐川用コードに変換
                        $shippingDeliveryTime = $shipping->getShippingDeliveryTime();

                        $shippingDeliveryTimeCode = "";
                        if ($shippingDeliveryTime == "午前") {
                            $shippingDeliveryTimeCode = "01";
                        } elseif ($shippingDeliveryTime == "12時～14時") {
                            $shippingDeliveryTimeCode = "12";
                        } elseif ($shippingDeliveryTime == "14時～16時") {
                            $shippingDeliveryTimeCode = "14";
                        } elseif ($shippingDeliveryTime == "16時～18時") {
                            $shippingDeliveryTimeCode = "16";
                        } elseif ($shippingDeliveryTime == "18時～21時") {
                            $shippingDeliveryTimeCode = "04";
                        }

                        $recordCsv['shippingInstructionNo'] = $this->generateZeroFillStr($order_item->getOrderId(), 6);
                        $recordCsv['expectedShippingDate'] = date("Ymd");;
                        if($shipping->getShippingDeliveryDate() != null){
                            $recordCsv['expectedArrivalDate'] = $shipping->getShippingDeliveryDate()->format("Ymd");
                        }
                        $recordCsv['arrivalTime'] = $shippingDeliveryTimeCode;
                        $recordCsv['storeCode'] = "";
                        $recordCsv['warehouseCode'] = $pc->getStockCode();
                        $recordCsv['saleCategory'] = '0';
                        $recordCsv['multiplicationRate'] = null;
                        $recordCsv['slipType'] = '0';
                        if($pc->getJanCode() != ""){
                            $recordCsv['productNumberCode'] = $pc->getJanCode();
                        }
                        else{
                            $recordCsv['productNumberCode'] = $pc->getCode();
                        }
                        $recordCsv['colorCode'] = "9999";
                        $recordCsv['sizeCode'] = '1';
                        $recordCsv['JAN_code'] = $pc->getJanCode();
                        $recordCsv['numberOfShippingInstructions'] = $order_item->getQuantity();
                        $recordCsv['retailPrice'] = intval($order_item->getPrice());
                        $recordCsv['deliveryUnitPrice'] = intval($order_item->getPrice());
                        $recordCsv['shippingCompanyCode'] = "000002";
                        $recordCsv['remarks'] = null;
                        $recordCsv['deliveryName'] = $shipping->getName01().$shipping->getName02();
                        $recordCsv['deliveryPostalCode'] =  substr($shipping->getPostalCode(),0,3) . "-" . substr($shipping->getPostalCode(),3);;
                        $recordCsv['deliveryAddress'] = $shipping->getPref()->getName().$shipping->getAddr01().$shipping->getAddr02();
                        $recordCsv['deliveryPhoneNumber'] = $shipping->getPhoneNumber();
                        $recordCsv['detailsRemarks'] = null;
                        $recordCsv['businessUnitName'] = null;
                        $recordCsv['areaName'] = null;
                        $recordCsv['destinationCode'] = null;
                        $recordCsv['areaCode'] = null;
                        $recordCsv['BBDATE'] = null;
                        $recordCsv['deliveryDestinationClassification'] = '1';
                        $recordCsv['totalProductPrice'] = intval($order->getPaymentTotal());
                        $recordCsv['discountAmount'] = 0;
                        $recordCsv['consumptionTax'] = intval($order->getTax());
                        $recordCsv['postage'] = ceil($order->getDeliveryFeeTotal() / 1.1);
                        $recordCsv['coupon'] = null;
                        $recordCsv['grossWeight'] = 1;
                        $recordCsv['numberOfUnits'] = 1;
                        if($order->getPaymentMethod() == "代金引換"){
                            $recordCsv['paymentMethodClassification'] = '2';
                        }
                        else{
                            $recordCsv['paymentMethodClassification'] = '0';
                        }
                        $recordCsv['remark_2'] = null;
                        $recordCsv['remark_3'] = null;
                        $recordCsv['salesDestinationClassification'] = '01';
                        $recordCsv['partNumberCode_2'] = $pc->getCode();
                        $recordCsv['commission'] = null;
                        $recordCsv['handlingFlightTypes'] = '000';
                        $recordCsv['destinationClassification'] = '1';
                        $recordCsv['slipOutputOrder'] = $this->generateZeroFillStr($i, 6);;

                        $sorted = [];
                        
                        $row = null;
                        foreach ($fieldSorted as $col) {
                            $row[] = $recordCsv[$col] ?? null; // null for blank field
                        }

                        var_dump($row);
                        $i++;
                        fputcsv($csvh, $row);
                    }
                }
            }

            fclose($csvh);
            echo 'Export succeeded.' . "\n";
        } catch (Exception $e) {
            /*
            $wms = new WmsSyncInfo();
            $wms->setSyncResult(3)
                ->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT)
                ->setSyncDate($now)
                ->setSyncLog($e->getMessage());
            echo $e->getMessage();
            $em->persist($wms);
            $em->flush();
            */
            print $e->getMessage();
            return false;
        }
        /*
        $wms = new WmsSyncInfo();
        $wms->setSyncResult(1)
            ->setSyncLog("")
            ->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT)
            ->setSyncDate($now);
        $em->persist($wms);
        $em->flush();
        */
    }

    /**
     * Generate a string with zero filled from a number.
     * @param int $num
     * @param int $length (without prefix)
     * @param string $prefix
     * @return string
     */
    private function generateZeroFillStr(int $num, int $length = 5, string $prefix = ''): string
    {
        return  $prefix . str_pad($num, $length, '0', STR_PAD_LEFT);
    }
}
