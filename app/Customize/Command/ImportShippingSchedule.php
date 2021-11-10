<?php

namespace Customize\Command;

use Customize\Repository\ShippingScheduleHeaderRepository;
use Customize\Repository\ShippingScheduleRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use DateTime;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Eccube\Repository\MemberRepository;
use Eccube\Repository\CustomerRepository;
use Symfony\Component\Console\Input\InputArgument;
use Customize\Config\AnilineConf;
use Customize\Service\MailService;
use Customize\Service\ProductStockService;

class ImportShippingSchedule extends Command
{
    protected static $defaultName = 'eccube:customize:import-shipping-schedule';

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
     * @var ShippingScheduleHeaderRepository
     */
    protected $shippingScheduleHeaderRepository;

    /**
     * @var ShippingScheduleRepository
     */
    protected $shippingScheduleRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var ProductStockService
     */
    protected $productStockService;

    /**
     * Import shipping schedule constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository
     * @param ShippingScheduleRepository $shippingScheduleRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     * @param ProductStockService $productStockService
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository,
        ShippingScheduleRepository       $shippingScheduleRepository,
        DnaCheckStatusHeaderRepository       $dnaCheckStatusHeaderRepository,
        CustomerRepository       $customerRepository,
        MailService       $mailService,
        ProductStockService       $productStockService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->shippingScheduleHeaderRepository = $shippingScheduleHeaderRepository;
        $this->shippingScheduleRepository = $shippingScheduleRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
        $this->productStockService = $productStockService;
    }

    protected function configure()
    {
        //$this->addArgument('fileName', InputArgument::REQUIRED, 'The fileName to import.')
        //    ->setDescription('Import csv shipping schedule.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // // タイムアウト上限を一時的に開放
        set_time_limit(0);

        $em = $this->entityManager;
        // 自動コミットをやめ、トランザクションを開始
        $em->getConnection()->setAutoCommit(false);

        // sql loggerを無効にする.
        $em->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->beginTransaction();

        // ファイル一覧取得
        $localDir = "var/tmp/wms/receive/";
        $fileNames = [];
        if ($handle = opendir($localDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    if(preg_match("/^SHUJIS_/", $entry)){
                        $fileNames[] = $entry;
                    }
                }
            }
            closedir($handle);
        }
var_dump($fileNames);
        if (!$fileNames) {
            return;
        }

        // ファイル一覧取得ここまで
        foreach ($fileNames as $fileName) {
            $csvpath = "var/tmp/wms/receive/" . $fileName;

            $fp = fopen($csvpath, 'r');
            if ($fp === false) {
                //エラー
                throw new Exception('Error: Failed to open file');
            }

            log_info('商品CSV取込開始');

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== false) {

                if(substr($data[1],0,1) == "5"){
                    //キット実績
                    $id = intval(substr($data[1],1));
var_dump($id);
                    $header = $this->dnaCheckStatusHeaderRepository->find($id);
                    $customer = $this->customerRepository->find($header->getRegisterId());

                    $header->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_SHIPPED);
                    $header->setKitShippingOperationDate(new \DateTime());

                    $em->persist($header);

                    $data = ["name" => $header->getShippingName()];
                    $this->mailService->sendDnaKitSendComplete($customer->getEmail(),$data);
                }
                else{
                    $dateShipping = new DateTime($data[2]);
                    $shippingHeader = $this->shippingScheduleHeaderRepository->find($data[0]);
                    if (!$shippingHeader) {
                        continue;
                    }
                    $shippingHeader->setShippingDate($dateShipping)
                        ->setWmsShipNo($data[4]);
                    $em->persist($shippingHeader);
                }
            }
            try {
                $em->flush();
                $em->getConnection()->commit();
                // $em->clear();
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }

            $logWmsDir = 'var/log/wms/';
            rename($csvpath, $logWmsDir . $fileName); // move files
        }

        // 端数分を更新
        try {
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        log_info('商品CSV取込完了');
        fclose($fp);
    }
}
