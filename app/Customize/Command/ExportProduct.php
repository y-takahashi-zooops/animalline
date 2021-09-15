<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductRepository;
use Exception;
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

    protected $productRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $newDate = date('Ymd_His');
        $dir = '/var/tmp/wms/items';
        $file = "SHNMST_$newDate.csv";
        $path = "$dir/$file";
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777)) throw new Exception('Can not create folder.');
        }
        if (!$targetFile = fopen($path, "w")) throw new Exception('Can not create file.');

        $data = [
            [1, 2, 3],
            [4, 5, 6]
        ];
        if ($data) {
            foreach ($data as $row) {
                fputcsv($targetFile, $row);
            }
            fclose($targetFile);
        }
    }
}
