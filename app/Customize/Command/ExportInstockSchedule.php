<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Entity\InstockSchedule;
use Customize\Entity\InstockScheduleHeader;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportInstockSchedule extends Command
{
    protected static $defaultName = 'eccube:customize:wms-export-instock-schedule';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var WmsSyncInfoRepository
     */
    protected $wmsSyncInfoRepository;

    /**
     * @var InstockScheduleHeader
     */
    protected $instockScheduleHeader;

    /**
     * @var InstockSchedule
     */
    protected $instockSchedule;

    /**
     * ExportRelease constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param InstockScheduleHeader $instockScheduleHeader
     * @param InstockSchedule $instockSchedule
     */
    public function __construct(
        EntityManagerInterface           $entityManager,
        WmsSyncInfoRepository            $wmsSyncInfoRepository,
        InstockScheduleHeader            $instockScheduleHeader,
        InstockSchedule                  $instockSchedule
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->instockScheduleHeader = $instockScheduleHeader;
        $this->instockSchedule = $instockSchedule;
    }

    protected function configure()
    {
        $this->setDescription('Instock schedule CSV export.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;
        $fields = [];

        $dir = 'var/tmp/wms/instock_schedule/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, 'R');
        }

        $now = Carbon::now();

        $syncInfo = $this->wmsSyncInfoRepository->findOneBy(['sync_action' => 4], ['sync_date' => 'DESC']);
    }
}
