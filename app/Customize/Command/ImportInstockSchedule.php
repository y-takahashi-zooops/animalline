<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var instockScheduleRepository
     */
    protected $instockScheduleRepository;

    /**
     * Import instock schedule constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     * 
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
    }

    protected function configure()
    {
        $this->addArgument('fileName', InputArgument::REQUIRED, 'The fileName to import.')
            ->setDescription('Import csv instock schedule.');
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
        $em->getConnection()->setAutoCommit(false);

        // sql loggerを無効にする.
        $em->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->beginTransaction();

        $csvpath = "var/tmp/wms/receive/" . $input->getArgument('fileName');

        // ファイルが指定されていれば続行
        if (!$csvpath) {
            throw new Exception('Error: File path is required');
        }

        $fp = fopen($csvpath, 'r');
        if ($fp === FALSE) {
            //エラー
            throw new \Exception('Error: Failed to open file');
        }

        log_info('商品CSV取込開始');

        // CSVファイルの登録処理
        while (($data = fgetcsv($fp)) !== FALSE) {
            $headerId = $data[0];
            $instockId = $data[5];
            $Header = $this->instockScheduleHeaderRepository->find($headerId);

            if (!$Header) {
                continue;
            }
            $Header->setArrivalDate(new DateTime($data[11]));
            $em->persist($Header);
            $Instock = $this->instockScheduleRepository->findOneBy(['id' => $instockId, 'InstockHeader' => $headerId]);

            if ($Instock) {
                $Instock->setItemCode01($data[8])
                    ->setArrivalQuantity($data[12])
                    ->setArrivalBox($data[13]);
                $em->persist($Instock);
            }

            // 端数分を更新
            try {
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }
        }

        log_info('商品CSV取込完了');
        fclose($fp);
        echo 'Import succeeded.' . "\n";
    }
}
