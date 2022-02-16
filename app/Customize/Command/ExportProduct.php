<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\WmsSyncInfoRepository;
use Customize\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportProduct extends Command
{
    protected static $defaultName = 'eccube:customize:wms-item';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var WmsSyncInfoRepository
     */
    protected $wmsSyncInfoRepository;

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;


    /**
     * ExportProduct constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param SupplierRepository $supplierRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WmsSyncInfoRepository  $wmsSyncInfoRepository,
        ProductClassRepository $productClassRepository,
        ProductRepository      $productRepository,
        SupplierRepository     $supplierRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->supplierRepository = $supplierRepository;
    }

    protected function configure()
    {
        $this->setDescription('Export csv product master');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;
        $fieldSorted = [
            'year', 'seasonCode', 'subSeasonCode', 'brandCode', 'subBrandCode', 'itemCode', 'subItemCode', 'productCode',
            'name', 'price02', 'price02Tax', 'item_cost', 'gender', 'taxCode', 'remarks', 'supplier_code', 'productNum',
            'colorCode', 'sizeCode', 'jan_code', 'quantity_box'
        ];

        $dir = 'var/tmp/wms/items/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, 'R');
        }

        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => AnilineConf::ANILINE_WMS_SYNC_ACTION_PRODUCT], ['sync_date' => 'DESC']);

        $qb = $this->productClassRepository->createQueryBuilder('pc');
        $qb->select(
            'p.id',
            'pc.code as productCode',
            'p.name',
            'pc.price02',
            '(pc.price02 * :with_tax) as price02Tax',
            'pc.item_cost',
            'pc.supplier_code',
            'pc.jan_code as jan_code'
        )
            ->leftJoin('pc.Product', 'p')
            ->where('p.update_date <= :to')
            ->setParameters(['with_tax' => AnilineConf::ANILINE_WMS_WITH_TAX, 'to' => Carbon::now()]);
        if ($syncInfo) $qb = $qb->andWhere('p.update_date >= :from')
            ->setParameter('from', $syncInfo->getSyncDate());
        $qb = $qb->orderBy('p.update_date', 'DESC');

        $records = $qb->getQuery()->getArrayResult();
        $filename = 'SHNMST_' . Carbon::now()->format('Ymd_His') . '.csv';
        if ($records) {
            $wms = new WmsSyncInfo();
            $wms->setSyncAction(AnilineConf::ANILINE_WMS_SYNC_ACTION_PRODUCT)
                ->setSyncDate(Carbon::now());
            //try {
            $csvPath = $dir . $filename;
            $csvh = fopen($csvPath, 'w+') or die("Can't open file");
            $d = ',';
            $e = '"';

            $result = [];
            foreach ($records as $record) {

                $supplier = $this->supplierRepository->findOneBy(['id' => $record['supplier_code']]);
                $product = $this->productRepository->find($record['id']);

                //$record['supplier_code'] = $supplier->getSupplierCode();
                $record['supplier_code'] = "0003";

                if (strlen($record['jan_code']) == 13) {
                    $record['productCode'] = $record['jan_code'];
                }
                $record['quantity_box'] = 1;

                $record['year'] = '99';
                $record['seasonCode'] = '01';
                $record['subSeasonCode'] = null;
                $record['brandCode'] = '0001';
                $record['subBrandCode'] = null;
                $record['itemCode'] = '0001';
                $record['subItemCode'] = null;
                $record['gender'] = '2';
                $record['taxCode'] = '01';
                $record['remarks'] = null;
                $record['productNum'] = null;
                $record['colorCode'] = '9999';
                $record['sizeCode'] = '1';
                $record['price02Tax'] = round($record['price02Tax'], 0);
                $record['price02'] = round($record['price02'], 0);
                $sorted = [];
                foreach ($fieldSorted as $value) {
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
            /*
            } catch (Exception $e) {
                $wms->setSyncResult(AnilineConf::ANILINE_WMS_RESULT_ERROR)
                    ->setSyncLog($e->getMessage());
                echo 'Export failed' . "\n";
            }
            */
            $em->persist($wms);
            $em->flush();
        }
    }
}
