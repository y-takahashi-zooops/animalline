<?php

namespace Customize\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class FtpDownloadUpload extends Command
{
    protected static $defaultName = 'eccube:customize:wms-ftp-download-upload';

    protected $tmpWmsDir = 'var/tmp/wms/';
    protected $logWmsDir = 'var/log/wms/';

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
        $this->setDescription('FTP download/upload wms command.');
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

        $productDir = 'items/';
        $shippingDir = 'shipping_schedule/';
        $instockDir = 'instock_schedule/';
        $returnDir = 'return_schedule/';

        $this->ftpUpload($productDir);
        $this->ftpUpload($shippingDir);
        $this->ftpUpload($instockDir);
        $this->ftpUpload($returnDir);

        echo "Successful.\n";
    }

    /**
     * @throws Exception
     */
    private function ftpDownload(string $remoteDir = '/OUT/', string $localDir = 'var/tmp/wms/receive/'): void
    {
        $HOST = env('FTP_HOST', 'test.rebex.net');
        $USERNAME = env('FTP_USERNAME', 'demo');
        $PASSWORD = env('FTP_PASSWORD', 'password');

        $ftp = ftp_connect($HOST);
        if (!$ftp || !ftp_login($ftp, $USERNAME, $PASSWORD)) {
            throw new Exception('access failed');
        }

        // turn on passive mode
        ftp_pasv($ftp, true);

        // scan remote files
        $finder = Finder::create()->files()->depth('== 0')->in("ftp://$USERNAME:$PASSWORD@$HOST" . $remoteDir);

        // create folder on local to save downloaded files if not exist
        if (!file_exists($localDir) && !mkdir($localDir, 0777, true)) {
            throw new Exception("Can't create directory.");
        }

        foreach ($finder as $file) {
            $localPath = $localDir . $file->getFilename();
            $remotePath = $remoteDir . $file->getFilename();
            // download a file
            if (ftp_get($ftp, $localPath, $remotePath, FTP_BINARY)) {
                //ftp_delete($ftp, $remotePath); // delete remote file TODO: UNCOMMENT ON REAL FTP SERVER
                echo "download success: from $remotePath to $localPath\n";
            } else {
                echo "download failed: from $remotePath to $localPath\n";
            }
        }

        ftp_close($ftp);
    }

    /**
     * @throws Exception
     */
    private function ftpUpload(string $directory, string $remoteDir = '/IN/'): void
    {
        $HOST = env('FTP_HOST', 'ftp.dlptest.com');
        $USERNAME = env('FTP_USERNAME', 'dlpuser');
        $PASSWORD = env('FTP_PASSWORD', 'rNrKYTX9g7z3RgJRmxWuGHbeu');

        $localDir = $this->tmpWmsDir . $directory;

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
            return;
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

        // create new folder on local to save moved files from old folder
        $newLocalDir = $this->logWmsDir . $directory;
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
                rename($localPath, $newLocalDir . $fileName); // move files
                echo "upload success: from $localPath to $remotePath\n";
            } else {
                echo "upload failed: from $localPath to $remotePath\n";
            }
        }

        ftp_close($ftp);
    }
}
