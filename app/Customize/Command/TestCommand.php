<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\WmsSyncInfoRepository;
use Customize\Repository\SupplierRepository;
use Customize\Service\ProductStockService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'eccube:customize:test';

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
     * @var ProductStockService
     */
    protected $productStockService;
    
    /**
     * ExportProduct constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param SupplierRepository $supplierRepository
     * @param ProductStockService $productStockService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WmsSyncInfoRepository  $wmsSyncInfoRepository,
        ProductClassRepository $productClassRepository,
        ProductRepository      $productRepository,
        SupplierRepository     $supplierRepository,
        ProductStockService     $productStockService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->supplierRepository = $supplierRepository;
        $this->productStockService = $productStockService;
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
        print intval(date("h"));
        /*
        $em = $this->entityManager;
        $pc = $this->productClassRepository->find(13);
        
        //$this->productStockService->calculateStock($em,$pc,1);
        $this->productStockService->setStock($em,$pc,20);
        /*
    }
}
