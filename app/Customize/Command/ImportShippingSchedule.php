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
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Repository\ShippingRepository;
use Customize\Repository\DnaSalesHeaderRepository;
use Customize\Repository\DnaSalesStatusRepository;
use Customize\Repository\BenefitsStatusRepository;
use Psr\Log\LoggerInterface;

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
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var ShippingRepository
     */
    protected $shippingRepository;

    /**
     * @var DnaSalesHeaderRepository
     */
    protected $dnaSalesHeaderRepository;

    /**
     * @var DnaSalesStatusRepository
     */
    protected $dnaSalesStatusRepository;

    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param OrderRepository $orderRepository
     * @param OrderStatusRepository $orderStatusRepository
     * @param ShippingRepository $shippingRepository
     * @param DnaSalesHeaderRepository $dnaSalesHeaderRepository
     * @param DnaSalesStatusRepository $dnaSalesStatusRepository
     * @param BenefitsStatusRepository $benefitsStatusRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository,
        ShippingScheduleRepository       $shippingScheduleRepository,
        DnaCheckStatusHeaderRepository       $dnaCheckStatusHeaderRepository,
        CustomerRepository       $customerRepository,
        MailService       $mailService,
        ProductStockService       $productStockService,
        OrderRepository                  $orderRepository,
        OrderStatusRepository $orderStatusRepository,
        ShippingRepository $shippingRepository,
        DnaSalesHeaderRepository         $dnaSalesHeaderRepository,
        DnaSalesStatusRepository         $dnaSalesStatusRepository,
        BenefitsStatusRepository $benefitsStatusRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->shippingScheduleHeaderRepository = $shippingScheduleHeaderRepository;
        $this->shippingScheduleRepository = $shippingScheduleRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
        $this->productStockService = $productStockService;
        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->shippingRepository = $shippingRepository;
        $this->dnaSalesHeaderRepository = $dnaSalesHeaderRepository;
        $this->dnaSalesStatusRepository = $dnaSalesStatusRepository;
        $this->benefitsStatusRepository = $benefitsStatusRepository;
        $this->logger = $logger;
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

        $OrderStatusDeliverd = $this->orderStatusRepository->find(OrderStatus::DELIVERED);

        // ファイル一覧取得ここまで
        foreach ($fileNames as $fileName) {
var_dump($fileName);
            $csvpath = "var/tmp/wms/receive/" . $fileName;

            $fp = fopen($csvpath, 'r');
            if ($fp === false) {
                //エラー
                throw new Exception('Error: Failed to open file');
            }

            $this->logger->info('商品CSV取込開始');

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== false) {

                if(substr($data[0],0,1) == "5"){
                    //キット実績
                    $id = intval(substr($data[0],1));

                    $header = $this->dnaCheckStatusHeaderRepository->find($id);
                    $customer = $this->customerRepository->find($header->getRegisterId());

                    $header->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_SHIPPED);
                    $header->setKitShippingOperationDate(new \DateTime());

                    $em->persist($header);
                    $em->flush();

                    $data = ["name" => $header->getShippingName()];
                    $this->mailService->sendDnaKitSendComplete($customer->getEmail(),$data);
                }
                elseif(substr($data[0],0,1) == "6"){
                    //成約特典実績
                    $id = intval(substr($data[0],1));

                    $Order = $this->benefitsStatusRepository->find($id);
                    $Order->setShippingStatus(3);

                    $dateShipping = new DateTime();
                    $Order->setBenefitsShippingOperationDate($dateShipping);

                    $em->persist($Order);
                    $em->flush();
                }
                elseif(substr($data[0],0,1) == "7"){
                    //有料販売DNA検査実績
                    $id = intval(substr($data[0],1));

                    $header = $this->dnaSalesHeaderRepository->find($id);
                    
                    $header->setShippingStatus(3);
                    $header->setKitShippingOperationDate(new \DateTime());

                    $em->persist($header);
                    $em->flush();

                    $Order = $this->orderRepository->find($header->getOrderId());
                    $Order->setOrderStatus($OrderStatusDeliverd);
                    $customer = $Order->getCustomer();

                    $data = ["name" => $header->getShippingName()];
                    $this->mailService->sendDnaKitSendCompleteBuy($customer->getEmail(),$data);
                }
                else{
                    $id = intval(substr($data[0],1));
                    $dateShipping = DateTime::createFromFormat("Ymd", $data[2]);
                    

                    $Order = $this->orderRepository->find($id);
                    $Order->setOrderStatus($OrderStatusDeliverd);

                    $Shipping = $this->shippingRepository->findOneby(['Order' => $Order]);
                    $Shipping->setShippingDate($dateShipping);
                    $Shipping->setTrackingNumber($data[4]);

                    $em->persist($Shipping);
                    $em->persist($Order);
                    $em->flush();

                    $this->mailService->sendShippingNotifyMail($Shipping);
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

        $this->logger->info('商品CSV取込完了');
        fclose($fp);
    }
}
