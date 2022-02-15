<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\WmsSyncInfoRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductStockRepository;
use Eccube\Repository\OrderItemRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Customize\Service\ProductStockService;

class ExportShippingSchedule extends Command
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
     * @var WmsSyncInfoRepository
     */
    protected $wmsSyncInfoRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ProductStockRepository
     */
    protected $productStockRepository;

    /**
     * @var ProductStockService
     */
    protected $productStockService;

    /**
     * @var OrderItemRepository
     */
    protected $orderItemRepository;
    
    /**
     * Export DNA kit shipping schedule constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ProductClassRepository $productClassRepository
     * @param ProductStockRepository $productStockRepository
     * @param ProductStockService $productStockService
     * @param OrderItemRepository $orderItemRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WmsSyncInfoRepository $wmsSyncInfoRepository,
        ProductClassRepository $productClassRepository,
        ProductStockRepository $productStockRepository,
        ProductStockService $productStockService,
        OrderItemRepository $orderItemRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productStockRepository = $productStockRepository;
        $this->productStockService = $productStockService;
        $this->orderItemRepository = $orderItemRepository;
    }

    protected function configure()
    {
        $this->setDescription('Export DNA kit shipping schedule CSV.');
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
        $now = Carbon::now();
        // mapped with WMS docs
        $cols = [
            'delivery_instruction_no', 'expected_shipping_date', 'expected_arrival_date', 'arrival_time', 'store_code',
            'warehouse_code', 'sale_category', 'hanging_rate', 'slip_type', 'product_number_code',
            'color_code', 'size_code', 'jan_code', 'kit_unit', 'retail_price',
            'delivery_unit_price', 'shipping_company_code', 'remark', 'shipping_name', 'shipping_zip',
            'delivery_address', 'shipping_tel', 'detailed_preparation', 'division_name', 'area_name',
            'destination_code', 'area_code', 'bbdate', 'delivery_destination_classification', 'total_product_amount',
            'discount_amount', 'sale_tax', 'postage', 'coupon', 'gross_weight',
            'number_units', 'payment_method_classification', 'remark_2', 'remark_3', 'sales_destination_classification',
            'part_number_code_2', 'commission', 'handling flight_type', 'destination_classification', 'slip_output_order'
        ];

        $qb = $this->dnaCheckStatusHeaderRepository->createQueryBuilder('dnah');
        $qb->select(
            'dnah.id as dna_header_id',
            'dnah.kit_unit',
            'dnah.shipping_name',
            'dnah.shipping_zip',
            'dnah.shipping_pref',
            'dnah.shipping_city',
            'dnah.shipping_address',
            'dnah.shipping_tel'
        )
            ->where('dnah.update_date <= :to')
            ->andWhere('dnah.shipping_status = :shipping_status')
            ->setParameters([
                'to' => $now,
                'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT
            ])
            ->orderBy('dnah.id', 'ASC');

        /*
        $SyncInfo = $this->wmsSyncInfoRepository->findOneBy(
            ['sync_action' => AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT],
            ['sync_date' => 'DESC']
        );
        if ($SyncInfo) {
            $qb = $qb->andWhere('dnah.update_date >= :from')
                ->setParameter('from', $SyncInfo->getSyncDate());
        }
        */

        if (!$records = $qb->getQuery()->getArrayResult()) {
            echo "Records not found.\n";
            return;
        }

        $em = $this->entityManager;
        // 自動コミットをやめ、トランザクションを開始
        $em->getConnection()->setAutoCommit(false);

        // sql loggerを無効にする.
        $em->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->beginTransaction();

        $rows = [];
        $dnaHeaderIds = [];

        $item_code = ["8790000","8790004","8790005","8790006"];
        foreach ($records as $record) {
            $dnaNo = $this->generateZeroFillStr($record['dna_header_id']);
            $nextDay = (new DateTime($now->toString() . ' +1 day'))->format('Ymd');
            
            $record['shipping_zip'] = substr($record['shipping_zip'],0,3) . "-" . substr($record['shipping_zip'],3);
            for($i=0;$i<4;$i++){
                $record['delivery_instruction_no'] = $dnaNo;
                $record['expected_shipping_date'] = date("Ymd");
                $record['warehouse_code'] = '00001';
                $record['sale_category'] = 0;
                $record['slip_type'] = 0;
                $record['product_number_code'] = $item_code[$i];
                $record['color_code'] = "9999";
                $record['size_code'] = 1;
                $record['retail_price'] = 0;
                $record['delivery_unit_price'] = 0;
                $record['shipping_company_code'] = '000003';
                $record['delivery_address'] = $record['shipping_pref'] . ' ' . $record['shipping_city'] . ' ' . $record['shipping_address'];
                $record['delivery_destination_classification'] = '1';
                $record['total_product_amount'] = 0;
                $record['discount_amount'] = 0;
                $record['sale_tax'] = 0;
                $record['postage'] = 0;
                $record['gross_weight'] = 1;
                $record['number_units'] = 1;
                $record['payment_method_classification'] = '0';
                $record['sales_destination_classification'] = '01';
                $record['part_number_code_2'] = $item_code[$i];
                $record['handling flight_type'] = '000';
                $record['destination_classification'] = '1';
                $record['slip_output_order'] = $dnaNo.$i;
                //キット以外はキット数量に関わらず１個
                if($i > 0) {
                    $record['kit_unit'] = 1;
                }

                //在庫チェック
                $pc = $this->productClassRepository->findOneBy(['code' => $item_code[$i]]);
                if($pc->getStock() < $record['kit_unit']){
                    throw new Exception("出荷に必要な在庫が不足しています。");
                }

                $this->productStockService->calculateStock($em, $pc, -$record['kit_unit']);

                $row = [];
                foreach ($cols as $col) {
                    $row[] = $record[$col] ?? null; // null for blank field
                }
                $rows[] = $row;

                $dnaHeaderIds[] = $record['dna_header_id'];
            }
        }

        $dir = 'var/tmp/wms/shipping_schedule/';
        if (!file_exists($dir) && !mkdir($dir, 0777, true)) {
            throw new Exception("Can't create directory.");
        }
        $filename = "SHUSJI_{$now->format('Ymd_His')}.csv";
        $csvPath = $dir . $filename;
        if (!$csvFile = fopen($csvPath, 'w+')) {
            throw new Exception("Can't create file.");
        }

        foreach ($rows as $row) {
            fputcsv($csvFile, $row);
        }
        fclose($csvFile);

        // reduce query duplicate records
        $uniqIds = array_unique($dnaHeaderIds);
        foreach ($uniqIds as $id) {
            $Header = $this->dnaCheckStatusHeaderRepository->find($id);
            $Header->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_INSTRUCTING);
            $em->persist($Header);
        }

        $Wms = (new WmsSyncInfo)
            ->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT)
            ->setSyncDate($now)
            ->setSyncResult(AnilineConf::ANILINE_WMS_RESULT_SUCCESS);
        $em->persist($Wms);

        // 端数分を更新
        try {
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        echo "Export succeeded.\n";
    }

    /**
     * Generate a string with zero filled from a number.
     * @param int $num
     * @param int $length (without prefix)
     * @param string $prefix
     * @return string
     */
    private function generateZeroFillStr(int $num, int $length = 5, string $prefix = '5'): string
    {
        return  $prefix . str_pad($num, $length, '0', STR_PAD_LEFT);
    }
}
