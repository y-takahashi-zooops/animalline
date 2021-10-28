<?php

namespace Customize\Command;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\WmsSyncInfo;
use Customize\Repository\InstockScheduleHeaderRepository;
use Customize\Repository\InstockScheduleRepository;
use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtpDownloadUpload extends Command
{
    protected static $defaultName = 'eccube:customize:wms-ftp-download-upload';

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
     * @var InstockScheduleHeaderRepository
     */
    protected $instockScheduleHeaderRepository;

    /**
     * @var instockScheduleRepository
     */
    protected $instockScheduleRepository;

    /**
     * ExportRelease constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WmsSyncInfoRepository $wmsSyncInfoRepository
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     * @param InstockScheduleRepository $instockScheduleRepository
     */
    public function __construct(
        EntityManagerInterface          $entityManager,
        WmsSyncInfoRepository           $wmsSyncInfoRepository,
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository,
        InstockScheduleRepository       $instockScheduleRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->wmsSyncInfoRepository = $wmsSyncInfoRepository;
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
        $this->instockScheduleRepository = $instockScheduleRepository;
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
        $this->ftpDownload();

        $this->ftpUpload();

        echo "Succeeded.\n";
    }

    private function ftpDownload()
    {
        // define some variables
        $server_file = $local_file = 'readme.txt';

        // set up basic connection
        $ftp = ftp_connect('test.rebex.net');

        // login with username and password
        $login_result = ftp_login($ftp, 'demo', 'password');
        var_dump($login_result);

        // turn on passive mode
        ftp_pasv($ftp, true);

        // try to download $server_file and save to $local_file
        if (ftp_get($ftp, $local_file, $server_file, FTP_BINARY)) {
            echo "Successfully written to $local_file\n";
        } else {
            echo "There was a problem\n";
        }

        // close the connection
        ftp_close($ftp);
    }

    private function ftpUpload()
    {
        $file = 'readme.txt';
        $remote_file = 'readme22.txt';

        // set up basic connection
        $ftp = ftp_connect('ftp.dlptest.com');

        // login with username and password
        $login_result = ftp_login($ftp, 'dlpuser', 'rNrKYTX9g7z3RgJRmxWuGHbeu');
        var_dump($login_result);

        // turn on passive mode
        ftp_pasv($ftp, true);

        // upload a file
        if (ftp_put($ftp, $remote_file, $file, FTP_ASCII)) {
            echo "successfully uploaded $file\n";
        } else {
            echo "There was a problem while uploading $file\n";
        }

        // close the connection
        ftp_close($ftp);
    }
}
