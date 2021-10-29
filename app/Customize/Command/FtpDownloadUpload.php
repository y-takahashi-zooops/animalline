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
        // download instock + return
        //$downInstockFrom = '/OUT/'; TODO: USE THIS ON REAL FPT SERVER
        $this->ftpDownload();

        // upload instock + return
        $uploadInstockFrom = 'var/tmp/wms/instock_schedule/';
        $this->ftpUpload($uploadInstockFrom);

        echo "Succeeded.\n";
    }

    private function ftpDownload(string $remoteDir = '/', string $localDir = 'var/tmp/wms/receive/'): bool
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
        $finder->files()->depth('== 0')->in("ftp://$USERNAME:$PASSWORD@$HOST" . $remoteDir);
        if (!$finder) {
            return true;
        }

        // create folder on local to save downloaded files if not exist
        if (!file_exists($localDir) && !mkdir($localDir, 0777, true)) {
            throw new Exception("Can't create directory.");
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

    private function ftpUpload(string $localDir = 'var/tmp/wms/receive/', string $remoteDir = '/IN/'): bool
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

        // create folder on remote to save uploaded files if not exist
        if (!ftp_nlist($ftp, $remoteDir)) {
            ftp_mkdir($ftp, $remoteDir);
        }

        //$this->ftp_mksubdirs($ftp, $remoteDir);

        // create folder on local to save downloaded files if not exist
        $newLocalDir = 'var/log/wms/instock_schedule/';
        if (!file_exists($newLocalDir) && !mkdir($newLocalDir, 0777, true)) {
            throw new Exception("Can't create directory.");
        }

        foreach ($fileNames as $fileName) {
            $localPath = $localDir . $fileName;
            $remotePath = $remoteDir . $fileName;
            // upload a file
            if (ftp_put($ftp, $remotePath, $localPath, FTP_ASCII)) {
                // delete local file
                //unlink($localPath);

                // move files
                rename($localPath, $newLocalDir . $fileName);

                echo "upload succeeded: from $localPath to $remotePath\n";
            } else {
                echo "upload failed: from $localPath to $remotePath\n";
            }
        }

        return ftp_close($ftp);
    }

    function ftp_mksubdirs($ftpcon, $ftpath, $ftpbasedir = '/IN')
    {
        @ftp_chdir($ftpcon, $ftpbasedir);
        $parts = explode('/', $ftpath);
        foreach ($parts as $part) {
            if (!@ftp_chdir($ftpcon, $part)) {
                ftp_mkdir($ftpcon, $part);
                ftp_chdir($ftpcon, $part);
                //ftp_chmod($ftpcon, 0777, $part);
            }
        }
    }
}
