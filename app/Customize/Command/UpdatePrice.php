<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductCategory;
use Customize\Repository\WmsSyncInfoRepository;
use Customize\Repository\SupplierRepository;
use Customize\Service\ProductStockService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductCategoryRepository;
use Eccube\Repository\CategoryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Eccube\Repository\MemberRepository;
use Psr\Log\LoggerInterface;

class UpdatePrice extends Command
{
    protected static $defaultName = 'eccube:customize:updateprice';

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
     * @var CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * ExportProduct constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param SupplierRepository $supplierRepository
     * @param ProductStockService $productStockService
     * @param CategoryRepository $categoryRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MemberRepository $memberRepository,
        WmsSyncInfoRepository  $wmsSyncInfoRepository,
        ProductClassRepository $productClassRepository,
        ProductRepository      $productRepository,
        SupplierRepository     $supplierRepository,
        ProductStockService     $productStockService,
        CategoryRepository   $categoryRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->supplierRepository = $supplierRepository;
        $this->productStockService = $productStockService;
        $this->memberRepository = $memberRepository;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
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
        // タイムアウト上限を一時的に開放
        set_time_limit(0);

        $totalCnt = 0;       // トータル行カウント
        $headerflag = false; // ヘッダースキップフラグ

        $em = $this->entityManager;
        // 自動コミットをやめ、トランザクションを開始
        $em->getConnection()->setAutoCommit(false);

        // sql loggerを無効にする.
        $em->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->beginTransaction();

        $csvpath = "var/tmp/price.csv";

        // ファイルが指定されていれば続行
        if ($csvpath) {
            $fp = fopen($csvpath, 'r');
            if ($fp === FALSE) {
                //エラー
                throw new \Exception('Error: Failed to open file');
            }

            $this->logger->info('商品価格CSV取込開始');
            $data = fgetcsv($fp);

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== FALSE) {
                if($data[0] != ""){
                    $totalCnt++;

                    $ProductClass = $this->productClassRepository->findOneby(['code' => $data[1]]);
                    $Product = $ProductClass->getProduct();

                    print($data[0]." : Price ".$ProductClass->getPrice01()." ==> ".$data[5]);
                    print($data[0]." : Cost ".$ProductClass->getItemCost()." ==> ".$data[6]);

                    $ProductClass->setPrice01(intval($data[5]));
                    $ProductClass->setPrice02(intval($data[5]));
                    $ProductClass->setItemCost(intval($data[6]));

                    $em->persist($ProductClass);

                    // 100件ごとに更新
                    if ($totalCnt % 100 == 0 && $totalCnt !== 0) {
                        var_dump($totalCnt);
                        try {
                            $em->flush();
                            $em->getConnection()->commit();
                            // $em->clear();
                        } catch (\Exception $e) {
                            $em->getConnection()->rollback();
                            throw $e;
                        }
                    }
                }
            }
        }

        // 端数分を更新
        try {
            $em->flush();
            $em->getConnection()->commit();
            // $em->clear();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        $this->logger->info('商品CSV取込完了');
        fclose($fp);
    }
}
