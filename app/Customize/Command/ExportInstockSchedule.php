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
        EntityManagerInterface          $entityManager,
        WmsSyncInfoRepository           $wmsSyncInfoRepository,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository
    )
    {
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
        $now = Carbon::now();
        $fields = [
            'invoiceNumber', 'invoiceDate', 'supplierCode', 'supplierName', 'boxNumber',
            'lineNumber', 'warehouseCode', 'stockDate', 'productNumberCode', 'colorCode',
            'sizeCode', 'jANCode', 'FOB', 'quantity', 'caseQuantity', 'BBDATE', 'remarks'
        ];

        $qb = $this->instockScheduleRepository->createQueryBuilder('isd');
        $qb->select(
            'ihd.id as invoiceNumber',
            's.supplier_code as supplierCode',
            's.supplier_name as supplierName',
            'isd.warehouse_code as warehouseCode',
            'ihd.arrival_date_schedule as stockDate',
            'isd.jan_code as productNumberCode',
            'isd.jan_code as jANCode',
            'isd.arrival_quantity_schedule as quantity',
            'isd.arrival_box_schedule as caseQuantity'
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

        if (!$records) {
            echo "No record instock export csv.\n";
            return;
        }
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
            $record['stockDate'] = $record['stockDate']->format('Y-m-d');

            $sorted = [];
            foreach ($fields as $value) {
                $sorted[] = $record[$value];
            }
            $result[] = $sorted;
        }

        $dir = 'var/tmp/wms/instock_schedule/';
        if (!file_exists($dir) && !mkdir($dir, 0777, true)) throw new Exception("Can't create directory.");
        $filename = "NYUKAYOTEI_{$now->format('Ymd_His')}.csv";
        $csvPath = $dir . $filename;
        if (!$csvh = fopen($csvPath, 'w+')) throw new Exception("Can't create file.");

        foreach ($result as $item) {
            fputcsv($csvh, $item);
        }
        fclose($csvh);

        $wms = (new WmsSyncInfo)
            ->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_INSTOCK_SCHEDULE)
            ->setSyncDate($now)
            ->setSyncResult(AnilineConf::ANILINE_WMS_RESULT_SUCCESS);
        $em = $this->entityManager;
        $em->persist($wms);
        $em->flush();

        echo "Export succeeded.\n";

    }
}
