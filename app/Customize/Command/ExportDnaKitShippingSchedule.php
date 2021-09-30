<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Repository\WmsSyncInfoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportDnaKitShippingSchedule extends Command
{
    protected static $defaultName = 'eccube:customize:wms-dnakit-shipping-schedule';

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
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * Export DNA kit shipping schedule constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WmsSyncInfoRepository $wmsSyncInfoRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        DnaCheckStatusRepository $dnaCheckStatusRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
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

        $qb = $this->dnaCheckStatusRepository->createQueryBuilder('dna');
        $qb->select(
            'dna.id as dna_id',
            'dnah.id as dna_header_id',
            'dnah.kit_unit',
            'dnah.shipping_name',
            'dnah.shipping_zip',
            'dnah.shipping_pref',
            'dnah.shipping_city',
            'dnah.shipping_address',
            'dnah.shipping_tel'
        )
            ->innerJoin('dna.DnaHeader', 'dnah')
            ->where('dna.update_date <= :to')
            ->andWhere('dnah.shipping_status = :shipping_status')
            ->setParameters([
                'to' => $now,
                'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT
            ])
            ->orderBy('dna.id', 'ASC');

        $SyncInfo = $this->wmsSyncInfoRepository->findOneBy(
            ['sync_action' => AnilineConf::ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT],
            ['sync_date' => 'DESC']
        );
        if ($SyncInfo) {
            $qb = $qb->andWhere('dna.update_date >= :from')
                ->setParameter('from', $SyncInfo->getSyncDate());
        }

        if (!$records = $qb->getQuery()->getArrayResult()) {
            echo "Records not found.\n";
            return;
        }

        $rows = [];
        $dnaHeaderIds = [];
        foreach ($records as $record) {
            $dnaNo = $this->generateZeroFillStr($record['dna_id']);
            $nextDay = (new DateTime($now->toString() . ' +1 day'))->format('Ymd');

            $record['delivery_instruction_no'] = $dnaNo;
            $record['expected_shipping_date'] = $nextDay;
            $record['warehouse_code'] = '00001';
            $record['sale_category'] = 0;
            $record['slip_type'] = 0;
            $record['product_number_code'] = '123456789';
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
            $record['part_number_code_2'] = '123456789';
            $record['handling flight_type'] = '000';
            $record['destination_classification'] = '1';
            $record['slip_output_order'] = $dnaNo;

            $row = [];
            foreach ($cols as $col) {
                $row[] = $record[$col] ?? null; // null for blank field
            }
            $rows[] = $row;

            $dnaHeaderIds[] = $record['dna_header_id'];
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

        $em = $this->entityManager;

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
        $em->flush();

        echo "Export succeeded.\n";
    }

    /**
     * Generate a string with zero filled from a number.
     * @param int $num
     * @param int $length (without prefix)
     * @param string $prefix
     * @return string
     */
    private function generateZeroFillStr(int $num, int $length = 5, string $prefix = '9'): string
    {
        return  $prefix . str_pad($num, $length, '0', STR_PAD_LEFT);
    }
}
