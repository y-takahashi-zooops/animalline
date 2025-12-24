<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\BusinessHolidayRepository;
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
     * @var BusinessHolidayRepository
     */
    protected $businessHolidayRepository;

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
     * @param BusinessHolidayRepository $businessHolidayRepository
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
        BusinessHolidayRepository $businessHolidayRepository,
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
        $this->businessHolidayRepository = $businessHolidayRepository;
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

        exec("heif-convert /var/www/animalline/var/tmp/IMG_0223.HEIC /var/www/animalline/var/tmp/IMG_0223.jpg");
        return;

        //カット
        $next_date = $this->businessHolidayRepository->getFutureBusinessDay(3);
        echo("Result::".$next_date->format('Y-m-d')."\n");

        return;

        /*
        $em = $this->entityManager;
        $pc = $this->productClassRepository->find(13);
        
        //$this->productStockService->calculateStock($em,$pc,1);
        $this->productStockService->setStock($em,$pc,20);
        */

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

        $csvpath = "var/tmp/itemimg.csv";

        $user = $this->memberRepository->find(1);
var_dump($csvpath);
        // ファイルが指定されていれば続行
        if ($csvpath) {
            $fp = fopen($csvpath, 'r');
            if ($fp === FALSE) {
                //エラー
                throw new \Exception('Error: Failed to open file');
            }

            $this->logger->info('商品画像CSV取込開始');

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== FALSE) {
                if($data[0] != ""){
                    $totalCnt++;
    var_dump($data);

                    $filePath = $data[1].".jpg";

                    $ProductClass = $this->productClassRepository->findOneby(['code' => $data[0]]);
                    $Product = $ProductClass->getProduct();

                    if(file_exists("/var/www/animalline_regist/html/upload/save_image/".$filePath)){
                        $ProductImage = new ProductImage();
                        
                        $ProductImage
                            ->setProduct($Product)
                            ->setCreator($user)
                            ->setFileName($filePath)
                            ->setSortNo(1);
                        $em->persist($ProductImage);
                    }

                    $c1 = substr($data[0],1,1);
                    $c2 = substr($data[0],0,1);
                    $c3 = substr($data[0],2,2);

                    $c2a = array("1"=>10,"2"=>11,"3"=>12,"4"=>13,"5"=>14,"6"=>31,"7"=>32,"8"=>33,"9"=>46);
                    $c2b = array("1"=>19,"2"=>20,"3"=>21,"4"=>22,"5"=>23,"6"=>31,"7"=>32,"8"=>33,"9"=>47);
                    $c2c = array("1"=>26,"2"=>27,"3"=>28,"4"=>29,"5"=>30,"6"=>31,"7"=>32,"8"=>33,"9"=>48);

                    $c3a = array("01"=>36,"02"=>37,"03"=>38,"04"=>39,"05"=>40,"06"=>41,"07"=>42,"08"=>0,"10"=> 0,"99"=>43);

                    switch($c1) {
                    case "1": 
                        $catid = $c2a[$c2];
                        break;
                    case "2": 
                        $catid = $c2b[$c2];
                        break;
                    case "3": 
                        $catid = $c2c[$c2];
                        break;
                    case "7": 
                        $catid = 44;
                        break;
                    case "8": 
                        $catid = 45;
                        break;
                    case "9": 
                        $catid = 34;
                        break;
                    }
    if($catid == 10){var_dump($catid);}

                    $category = $this->categoryRepository->find($catid);
                    
                    $pc = new ProductCategory();
                    $pc->setProduct($Product);
                    $pc->setProductId($Product->getId());
                    
                    $pc->setCategory($category);
                    $pc->setCategoryId($category->getId());
                    
                    $em->persist($pc);
    //var_dump($c3a[$c3]);

                    if($c3a[$c3] != 0){
                        $category = $this->categoryRepository->find($c3a[$c3]);

                        $pc = new ProductCategory();
                        $pc->setProduct($Product);
                        $pc->setProductId($Product->getId());
                        
                        $pc->setCategory($category);
                        $pc->setCategoryId($category->getId());
                        
                        $em->persist($pc);
                    }
    if($Product->getId() == 1350){var_dump($Product->getId());}

                        if($c3 == "01"){
                            $Product->setIsCheckAuth(1);
                            $em->persist($Product);
                        }
                    // 画像をダウンロードして所定のパスに配置
                    // if (!empty($data[26])) {

                    //     $urls = explode(" ", $data[26]);

                    //     foreach ($urls as $url) {

                    //         $data = file_get_contents($url);
                    //         $fileName = str_replace('/', '', strrchr($url, '/'));
                    //         $filePath = 'html/upload/save_image/' . $fileName;

                    //         file_put_contents($filePath, $data);
                    //         $ProductImage = new ProductImage();
                    //         $ProductImage
                    //             ->setProduct($Product)
                    //             ->setCreator($user)
                    //             ->setFileName($fileName)
                    //             ->setSortNo(1);

                    //         $em->persist($ProductImage);
                    //         // $em->flush();
                    //     }
                    // }


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
