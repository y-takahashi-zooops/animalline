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

class ImportProduct extends Command
{
    protected static $defaultName = 'eccube:customize:import-item';

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
                    //画像ファイルがあるものだけ登録
                    $img_path = "var/img/".$data[12].".jpg";

                    if(file_exists($img_path)){
                        $totalCnt++;
                        echo "画像あり：".$data[0]."\n";

                        
                        // productテーブルに新規レコード追加
                        $Product = new Product();

                        // product_classテーブルに新規レコード追加
                        $ProductClass = new ProductClass();
                        $ProductClass->setProduct($Product);  // 商品ID

                        // product_stockテーブルに新規レコード追加
                        $ProductStock = new ProductStock();

                        $ProductImage = new ProductImage();
                        $productCategory = new ProductCategory();

                        // 重量kg換算＋単位削除
                        /*
                        if (strpos($data[13], 'kg') !== false) {
                            $item_weight = str_replace('kg', '', $data[13]);
                        } else {
                            $item_weight = str_replace('g', '', $data[13]);
                            $item_weight = $item_weight / 1000;
                        }
                        */
                        // 金額カンマ,円マーク削除
                        $price01 = str_replace('\\', '', str_replace(',', '', $data[6]));
                        $price02 = str_replace('\\', '', str_replace(',', '', $data[7]));
                        $itemCost = str_replace('\\', '', str_replace(',', '', $data[8]));
                        
                        // productテーブルにデータセット
                        $Product
                            ->setCreator($user)                                // 作成者ID
                            ->setStatus($ProductStatus)                        // 商品ステータスID：1(公開)
                            ->setName($data[1])                                // 商品名
                            ->setDescriptionDetail($data[2])                   // 商品説明
                            ->setSearchWord($data[3])                          // 検索ワード
                            //->setQuantityBox($data[12])                        // ケース入数
                            ->setItemWeight($data[10])                      // 重量
                            ->setMakerId($data[11]);                     // メーカーID
                        // product_classテーブルにデータセット
                        
                        $ProductClass
                            ->setStockCode('00001')                            // 論理棚
                            ->setIncentiveRatio(15)                            // インセンティブ率
                            ->setSaleType($SaleType)                            // 販売種別ID：1
                            ->setCreator($user)                                 // 作成者ID
                            ->setJanCode($data[5])
                            ->setCode($data[0])                                 // 商品コード
                            ->setJanCode($data[5])                                 // JANコード
                            //->setStock($data[6])                                // 在庫数
                            ->setStockUnlimited(1)                              // 在庫制限
                            ->setPrice01(intval($price01))                             // 価格1 
                            ->setPrice02(intval($price02))                              // 価格2 
                            ->setVisible(true)                                  // 表示フラグ 
                            ->setCurrencyCode('JPY')                            // 通貨コード
                            ->setSupplierCode($data[9])                        // 仕入先コード
                            ->setItemCost(intval($itemCost));                           // 仕入価格

                        // product_stockテーブルにデータセット
                        $ProductStock->setProductClass($ProductClass)           // 商品クラスID
                            //->setStock(intval($data[6]))
                            ->setCreator($user);                                // 作成者ID

                        $filePath = $data[12].".jpg";
                        $ProductImage
                            ->setProduct($Product)
                            ->setCreator($user)
                            ->setFileName($filePath)
                            ->setSortNo(1);

                        

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

                        $em->persist($Product);
                        $em->persist($ProductClass);
                        $em->persist($ProductStock);
                        $em->persist($ProductImage);
                    }
                    else{
                        echo "画像なし：".$data[0]. " - ".$img_path."\n";
                    }
                }
                // 2行目以降を読み込む
                $headerflag = true;
            }
            $em->flush();
        }

        $this->logger->info('商品CSV取込完了');
        fclose($fp);
    }
}
