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

    public function __construct(
        EntityManagerInterface $entityManager,
        WmsSyncInfoRepository  $wmsSyncInfoRepository,
        ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository,
        ShippingScheduleRepository     $shippingScheduleRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->shippingScheduleHeaderRepository = $shippingScheduleHeaderRepository;
        $this->shippingScheduleRepository = $shippingScheduleRepository;
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
            if (!mkdir($dir, 0777)) throw new Exception('Can not create folder.');
        }

        $syncDate = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => 1], ['sync_date' => 'ASC'])->getSyncDate();

//        $qb = $this->productClassRepository->createQueryBuilder('pc');
//        $qb->select('COALESCE(pc.code, pc.id) as productCode', 'p.name', 'pc.price02', 'pc.price02 as price02Tax',
//            'pc.item_cost', 'pc.supplier_code', 'pc.code as jan_code', 'p.quantity_box')
//            ->leftJoin('pc.Product', 'p')
//            ->add('where', $qb->expr()->between(
//                'p.update_date',
//                ':from',
//                ':to')
//            )
//            ->setParameters(array('from' => $syncDate, 'to' => Carbon::now()))
//            ->orderBy('p.update_date', 'DESC');

//        $records = $qb->getQuery()->getArrayResult();
        $filename = 'SHNMST_' . Carbon::now()->format('Ymd_His') . '.csv';
//        if ($records) {
//            $wms = new WmsSyncInfo();
//            $wms->setSyncAction(1)
//                ->setSyncDate(Carbon::now());
//            try {
//                $csvPath = $dir . '/' . $filename;
//                $csvh = fopen($csvPath, 'w+') or die("Can't open file");
//                $d = ','; // this is the default but i like to be explicit
//                $e = '"'; // this is the default but i like to be explicit
//
//                $result = [];
////                foreach ($records as $record) {
////                    $record['year'] = '99';
////                    $record['seasonCode'] = '01';
////                    $record['subSeasonCode'] = null;
////                    $record['brandCode'] = '0001';
////                    $record['subBrandCode'] = null;
////                    $record['itemCode'] = '0001';
////                    $record['subItemCode'] = null;
////                    $record['gender'] = '2';
////                    $record['taxCode'] = '01';
////                    $record['remarks'] = null;
////                    $record['productNum'] = null;
////                    $record['colorCode'] = '9999';
////                    $record['sizeCode'] = '1';
////                    $sorted = [];
////                    foreach ($fieldSorted as $value) {
////                        array_push($sorted, $record[$value]);
////                    }
//                    array_push($result, $sorted);
//                }
//                foreach ($result as $item) {
//                    fputcsv($csvh, $item, $d, $e);
//                }
//                fclose($csvh);
//
//                $wms->setSyncResult(1);
//            } catch (Exception $e) {
//                $wms->setSyncResult(3)
//                    ->setSyncLog($e->getMessage());
//            }
//            $em->persist($wms);
//            $em->flush();
//        }
    }
}