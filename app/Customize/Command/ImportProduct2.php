<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductCategory;

use Eccube\Repository\MemberRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SaleTypeRepository;
use Eccube\Repository\ProductStockRepository;
use Eccube\Repository\ProductImageRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Util\StringUtil;

use Psr\Log\LoggerInterface;

class ImportProduct2 extends Command
{
    protected static $defaultName = 'eccube:customize:import-item2';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var MemberRepository
     */
    protected $memberRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var SaleTypeRepository
     */
    protected $saleTypeRepository;

    /**
     * @var ProductStockRepository
     */
    protected $productStockRepository;

    /**
     * @var ProductImageRepository
     */
    protected $productImageRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ImportProduct constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MemberRepository $memberRepository
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param SaleTypeRepository $saleTypeRepository
     * @param ProductStockRepository $productStockRepository
     * @param ProductImageRepository $productImageRepository
     * @param CategoryRepository $categoryRepository
     * @param LoggerInterface $logger
     * 
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MemberRepository $memberRepository,
        ProductClassRepository $productClassRepository,
        ProductRepository $productRepository,
        ProductStatusRepository $productStatusRepository,
        SaleTypeRepository $saleTypeRepository,
        ProductStockRepository $productStockRepository,
        ProductImageRepository $productImageRepository,
        CategoryRepository $categoryRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->memberRepository = $memberRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->saleTypeRepository = $saleTypeRepository;
        $this->productStockRepository = $productStockRepository;
        $this->productImageRepository = $productImageRepository;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setDescription('Import csv product master');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // タイムアウト上限を一時的に開放
        set_time_limit(0);

        $totalCnt = 0;       // トータル行カウント
        $headerflag = false; // ヘッダースキップフラグ

        $em = $this->entityManager;
        $csvpath = "var/items.csv";

        // ファイルが指定されていれば続行
        if ($csvpath) {
            $fp = fopen($csvpath, 'r');
            if ($fp === FALSE) {
                //エラー
                throw new \Exception('Error: Failed to open file');
            }

            $this->logger->info('商品CSV取込開始');

            $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW);
            $SaleType = $this->saleTypeRepository->find(SaleType::SALE_TYPE_NORMAL);
            $user = $this->memberRepository->find(1);

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== FALSE) {
                
                // ヘッダー行(1行目)はスキップ
                if ($headerflag) {
                    $pc = $data[0];
                    $ProductClass = $this->productClassRepository->findOneBy(array("code" => $pc));

                    if($ProductClass){
                        $Product = $ProductClass->getProduct();
                        $productCategory = new ProductCategory();
                        
                        $category = $this->categoryRepository->find($data[4]);

                        var_dump($Product->getId());
                        var_dump($category->getId());
                        $productCategory->setProduct($Product);
                        $productCategory->setProductId($Product->getId());
                        $productCategory->setCategory($category);
                        $productCategory->setCategoryId($category->getId());

                        $em->persist($productCategory);
                    }
                }
                // 2行目以降を読み込む
                $headerflag = true;
            }
        }
        $em->flush();
        
        $this->logger->info('商品CSV取込完了');
        fclose($fp);
    }
}
