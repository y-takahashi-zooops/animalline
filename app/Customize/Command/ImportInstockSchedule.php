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
        $this->setDescription('Import instock schedule.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // // タイムアウト上限を一時的に開放
        set_time_limit(0);

        $totalCnt = 0;       // トータル行カウント
        $headerflag = false; // ヘッダースキップフラグ

        $em = $this->entityManager;
        // 自動コミットをやめ、トランザクションを開始
        $em->getConnection()->setAutoCommit(false);

        // sql loggerを無効にする.
        $em->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->beginTransaction();

        // todo: update path
        $csvpath = "var/tmp/items.csv";

        // ファイルが指定されていれば続行
        if ($csvpath) {
            $fp = fopen($csvpath, 'r');
            if ($fp === FALSE) {
                //エラー
                throw new \Exception('Error: Failed to open file');
            }

            log_info('商品CSV取込開始');

            // CSVファイルの登録処理
            while (($data = fgetcsv($fp)) !== FALSE) {
                // ヘッダー行(1行目)はスキップ
                if ($headerflag) {
                    $totalCnt++;

                    // todo: add logic
                }
                // 2行目以降を読み込む
                $headerflag = true;


                // 100件ごとに更新
                if ($totalCnt % 100 == 0 && $totalCnt !== 0) {
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

        // 端数分を更新
        try {
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        log_info('商品CSV取込完了');
        fclose($fp);
    }
}
