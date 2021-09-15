<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        WmsSyncInfoRepository  $wmsSyncInfoRepository,
        ProductClassRepository $productClassRepository,
        ProductRepository      $productRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
    }

    protected function configure()
    {
        $this->setDescription('Export csv product master');
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
        $dir = 'var/tmp/wms/items';

        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777)) throw new Exception('Can not create folder.');
        }

        $syncDate = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => 1], ['sync_date' => 'ASC'])->getSyncDate();
        $newDate = Carbon::now();

        $qb = $this->productClassRepository->createQueryBuilder('pc');
        $qb
            ->select('productCode')
            ->leftJoin('pc.Product', 'p')
            ->add('where', $qb->expr()->between(
                'p.update_date',
                ':from',
                ':to')
            )
            ->setParameters(array('from' => $syncDate, 'to' => $newDate))
            ->orderBy('p.update_date', 'DESC');

        $records = $qb->getQuery()->getArrayResult();
//        echo (getcwd());
        $filename = 'SHNMST_' . Carbon::now()->format('Ymd_His') . '.csv';
        if ($records) {
            $csvPath = 'var/tmp/wms/items/' . $filename;

            $csvh = fopen($csvPath, 'w+') or die("Can't open file");
            $d = ','; // this is the default but i like to be explicit
            $e = '"'; // this is the default but i like to be explicit

            foreach ($records as $record) {
                fputcsv($csvh, $record, $d, $e);
            }

            fclose($csvh);

            // do something with the file
        }
    }
}
