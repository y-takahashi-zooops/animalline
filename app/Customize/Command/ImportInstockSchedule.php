<?php

namespace Customize\Command;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductStockRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Eccube\Repository\MemberRepository;
use Customize\Repository\InstockScheduleHeaderRepository;
use Customize\Repository\InstockScheduleRepository;
use DateTime;
use Symfony\Component\Console\Input\InputArgument;
use Psr\Log\LoggerInterface;

class ImportInstockSchedule extends Command
{
    protected static $defaultName = 'eccube:customize:import-instock-schedule';

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
     * @var InstockScheduleHeaderRepository
     */
    protected $instockScheduleHeaderRepository;

    /**
     * @var InstockScheduleRepository
     */
    protected $instockScheduleRepository;

    /**
     * @var ProductStockRepository
     */
    protected $productStockRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Import instock schedule constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     * @param ProductStockRepository $productStockRepository
     * @param LoggerInterface $logger
     *
     */
    public function __construct(
        EntityManagerInterface          $entityManager,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository,
        ProductStockRepository          $productStockRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
        $this->productStockRepository = $productStockRepository;
        $this->logger = $logger;
    }

    protected function configure()
    {
        //$this->addArgument('fileName', InputArgument::REQUIRED, 'The fileName to import.')
        //    ->setDescription('Import csv instock schedule.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // タイムアウト上限を一時的に開放
        set_time_limit(0);

        $em = $this->entityManager;
        // 自動コミットをやめ、トランザクションを開始
        //$em->getConnection()->setAutoCommit(false);

        // sql loggerを無効にする.
        //$em->getConfiguration()->setSQLLogger(null);
        //$em->getConnection()->beginTransaction();

        // ファイル一覧取得
        $localDir = "var/tmp/wms/receive/";
        $fileNames = [];
        if ($handle = opendir($localDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    if(preg_match("/^NKOJIS_/", $entry)){
                        $fileNames[] = $entry;
                    }
                }
            }
            closedir($handle);
        }
        if (!$fileNames) {
            return;
        }
var_dump($fileNames);
        // ファイル一覧取得ここまで
        foreach ($fileNames as $fileName) {
            $csvpath = $localDir . $fileName;

            // ファイルが指定されていれば続行
            if (!$csvpath) {
                throw new Exception('Error: File path is required');
            }

            $fp = fopen($csvpath, 'r');
            if ($fp === FALSE) {
                //エラー
                throw new Exception('Error: Failed to open file');
            }

            $this->logger->info('商品CSV取込開始');

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== FALSE) {
                var_dump($data);

                $headerId = $data[0];   //ヘッダのID
                $instockId = $data[8];  //アイテムコード
                $Header = $this->instockScheduleHeaderRepository->find($headerId);

                if (!$Header) {
                    $this->logger->info('ID ['.$data[0].'] が見つかりません');
                    continue;
                }
                $rt = $Header->getRemarkText();
                var_dump($rt);

                if($data[11] != "") {
                    echo '入荷日更新'."\n";

                    $Header->setArrivalDate(DateTime::createFromFormat("Ymd",$data[11]));
                    $dd = $Header->getArrivalDate();
                    var_dump($dd);

                    $em->persist($Header);
                
                    $Instock = $this->instockScheduleRepository->findOneBy(['InstockHeader' => $Header, 'item_code_01' => $instockId]);
                    if ($Instock) {
                        $Instock->setArrivalQuantity($data[12] ? $data[12] : NULL);
                        //$ProductStock = $this->productStockRepository->findOneBy(['ProductClass' => $Instock->getProductClass()]);
                        //$ProductStock->setStock($ProductStock->getStock() + $Instock->getArrivalQuantity());
                        $em->persist($Instock);
                        //$em->persist($ProductStock);
                    }

                    $em->flush();
                }
            }
            $logWmsDir = 'var/log/wms/';
            rename($csvpath, $logWmsDir . $fileName); // move files
        }

        $this->logger->info('商品CSV取込完了');
        fclose($fp);
        echo 'Import succeeded.' . "\n";
    }
}
