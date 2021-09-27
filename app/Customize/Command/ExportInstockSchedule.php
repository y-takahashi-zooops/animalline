<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\InstockSchedule;
use Customize\Entity\InstockScheduleHeader;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\InstockScheduleHeaderRepository;
use Customize\Repository\InstockScheduleRepository;
use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportInstockSchedule extends Command
{
    protected static $defaultName = 'eccube:customize:wms-export-instock-schedule';

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
     * @var InstockScheduleHeaderRepository
     */
    protected $instockScheduleHeaderRepository;

    /**
     * @var instockScheduleRepository
     */
    protected $instockScheduleRepository;

    /**
     * ExportRelease constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        WmsSyncInfoRepository            $wmsSyncInfoRepository,
        InstockScheduleHeaderRepository  $instockScheduleHeaderRepository,
        InstockScheduleRepository        $instockScheduleRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
    }

    protected function configure()
    {
        $this->setDescription('Instock schedule CSV export.');
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
        $fields = [
            'invoiceNumber', 'invoiceDate', 'supplierCode', 'supplierName', 'boxNumber',
            'lineNumber', 'warehouseCode', 'stockDate', 'productNumber', 'code',
            'colorCode', 'sizeCode', 'jANCode', 'FOB', 'quantity', 'caseQuantity',
            'BBDATE', 'remarks'
        ];

        $dir = 'var/tmp/wms/instock_schedule/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, 'R');
        }

        $now = Carbon::now();

        $qb = $this->instockScheduleRepository->createQueryBuilder('isd');
        $qb->select(
            'ihd.id as invoice_number',
            's.supplier_code',
            's.supplier_name',
            'isd.warehouse_code',
            'ihd.arrival_date_schedule',
            'isd.jan_code as product_number_code',
            'isd.jan_code as jan_code',
            'isd.arrival_quantity_schedule',
            'isd.arrival_box_schedule'
        )
            ->innerJoin('isd.InstockHeader', 'ihd')
            ->leftJoin('Customize\Entity\Supplier', 's', 'WITH', 'ihd.supplier_code = s.supplier_code')
            ->where('isd.update_date <= :to')
            ->setParameters(['to' => $now])
            ->orderBy('isd.update_date', 'DESC');
        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => AnilineConf::ANILINE_WMS_SYNC_ACTION_INSTOCK_SCHEDULE], ['sync_date' => 'DESC']);
        if ($syncInfo) {
            $qb = $qb->andWhere('isd.update_date >= :from')
                ->setParameter('from', $syncInfo->getSyncDate());
        }
        $records = $qb->getQuery()->getArrayResult();

        $filename = 'NYUKAYOTEI_' . Carbon::now()->format('Ymd_His') . '.csv';
        if ($records) {
            $wms = new WmsSyncInfo();
            $wms->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_INSTOCK_SCHEDULE)
                ->setSyncDate(Carbon::now());
            //try {
            $csvPath = $dir . $filename;
            $csvh = fopen($csvPath, 'w+') or die("Can't open file");
            $d = ',';
            $e = '"';

            $result = [];
            foreach ($records as $record) {
                $record['invoiceDate'] = null;
                $record['boxNumber'] = null;
                $record['lineNumber'] = null;
                $record['colorCode'] = 9999;
                $record['sizeCode'] = 1;
                $record['FOB'] = null;
                $record['BBDATE'] = null;
                $record['remarks'] = null;
                $sorted = [];
                foreach ($fields as $value) {
                    array_push($sorted, $record[$value]);
                }
                array_push($result, $sorted);
            }
            foreach ($result as $item) {
                fputcsv($csvh, $item, $d, $e);
            }
            fclose($csvh);

            $wms->setSyncResult(AnilineConf::ANILINE_WMS_RESULT_SUCCESS);
            echo 'Export succeeded.' . "\n";

            $em->persist($wms);
            $em->flush();
        }
    }
}
