<?php

namespace Customize\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtpDownloadUpload extends Command
{
    protected static $defaultName = 'eccube:customize:wms-ftp-download-upload';

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * FTP download upload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('FTP download upload batch.');
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
        // TODO: PASS PARAMS TO THESE FUNCTIONS ON REAL FPT SERVER
        // download instock + return
        //$downInstockFrom = '/OUT/var/log/wms/instock_schedule';
        //$downInstockTo = 'var/tmp/wms/receive/';
        //$this->ftpDownload($downInstockFrom, $downInstockTo);
        $this->ftpDownload();

        // upload instock + return
        //$uploadInstockFrom = 'var/tmp/wms/instock_schedule';
        //$uploadInstockTo = '/IN/var/log/wms/instock_schedule';
        //$this->ftpUpload($uploadInstockFrom, $uploadInstockTo);
        $this->ftpUpload();

        echo "Succeeded.\n";
    }

    private function ftpDownload(string $remoteDir = '/pub/example/', string $localDir = 'var/tmp/wms/receive/'): bool
    {
        // TODO: load from .env file
        $HOST = 'test.rebex.net';
        $USERNAME = 'demo';
        $PASSWORD = 'password';

        $ftp = ftp_connect($HOST);
        if (!$ftp || !ftp_login($ftp, $USERNAME, $PASSWORD)) {
            throw new Exception('access failed');
        }

        // turn on passive mode
        ftp_pasv($ftp, true);

        // scan remote files
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()->in("ftp://$USERNAME:$PASSWORD@$HOST" . $remoteDir);
        if (!$finder) {
            return true;
        }

        foreach ($finder as $file) {
            $localPath = $localDir . $file->getFilename();
            $remotePath = $remoteDir . $file->getFilename();
            // download a file
            if (ftp_get($ftp, $localPath, $remotePath, FTP_BINARY)) {
                // delete remote file
                //ftp_delete($ftp, $remotePath); TODO: UNCOMMENT ON REAL FPT SERVER
                echo "download succeeded: from $remotePath to $localPath\n";
            } else {
                echo "download failed: from $remotePath to $localPath\n";
            }
        }

        return ftp_close($ftp);
    }

    private function ftpUpload(string $localDir = 'var/tmp/wms/receive/', string $remoteDir = '/'): bool
    {
        // TODO: load from .env file
        $HOST = 'ftp.dlptest.com';
        $USERNAME = 'dlpuser';
        $PASSWORD = 'rNrKYTX9g7z3RgJRmxWuGHbeu';

        // scan local files
        $fileNames = [];
        if ($handle = opendir($localDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $fileNames[] = $entry;
                }
            }

            closedir($handle);
        }
        if (!$fileNames) {
            return true;
        }

        $ftp = ftp_connect($HOST);
        if (!$ftp || !ftp_login($ftp, $USERNAME, $PASSWORD)) {
            throw new Exception('access failed');
        }

        // turn on passive mode
        ftp_pasv($ftp, true);

        foreach ($fileNames as $fileName) {
            $localPath = $localDir . $fileName;
            $remotePath = $remoteDir . $fileName;
            // upload a file
            if (ftp_put($ftp, $remotePath, $localPath, FTP_ASCII)) {
                // delete local file
                unlink($localPath);
                echo "upload succeeded: from $localPath to $remotePath\n";
            } else {
                echo "upload failed: from $localPath to $remotePath\n";
            }
        }

        return ftp_close($ftp);
    }
}
