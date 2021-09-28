<?php

namespace Customize\Command;

use Customize\Repository\ShippingScheduleHeaderRepository;
use Customize\Repository\ShippingScheduleRepository;
use DateTime;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Eccube\Repository\MemberRepository;
use Symfony\Component\Console\Input\InputArgument;

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
     * Import shipping schedule constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository
     * @param ShippingScheduleRepository $shippingScheduleRepository
     *
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        ShippingScheduleHeaderRepository $shippingScheduleHeaderRepository,
        ShippingScheduleRepository       $shippingScheduleRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->shippingScheduleHeaderRepository = $shippingScheduleHeaderRepository;
        $this->shippingScheduleRepository = $shippingScheduleRepository;
    }

    protected function configure()
    {
        $this->addArgument('fileName', InputArgument::REQUIRED, 'The fileName to import.')
            ->setDescription('Import csv shipping schedule.');
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

        $csvpath = "var/tmp/wms/receive/" . $input->getArgument('fileName');

        // ファイルが指定されていれば続行
        if ($csvpath) {
            $fp = fopen($csvpath, 'r');
            if ($fp === false) {
                //エラー
                throw new Exception('Error: Failed to open file');
            }

            log_info('商品CSV取込開始');

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== false) {
                // ヘッダー行(1行目)はスキップ
                $dateShipping = new DateTime($data[2]);
                $shippingHeader = $this->shippingScheduleHeaderRepository->find($data[0]);
                if (!$shippingHeader) {
                    continue;
                }
                $shippingHeader->setShippingDate($dateShipping)
                    ->setWmsShipNo($data[4]);
                $em->persist($shippingHeader);

                try {
                    $em->flush();
                    $em->getConnection()->commit();
                    // $em->clear();
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    throw $e;
                }
            }
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
