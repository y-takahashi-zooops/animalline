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

    protected $ftp;

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
        $HOST = $_ENV['FTP_HOST'] ?? getenv('FTP_HOST');
        $USERNAME = $_ENV['FTP_USERNAME'] ?? getenv('FTP_USERNAME');
        $PASSWORD = $_ENV['FTP_PASSWORD'] ?? getenv('FTP_PASSWORD');

        $download_dir_remote = "/OUT/";
        $download_dir_local = 'var/tmp/wms/receive/';

        $this->ftp = ftp_ssl_connect($HOST);
        if (!$this->ftp) {
            throw new Exception('サーバ（'.$HOST.'）が見つかりませんでした。');
        }

        if (!ftp_login($this->ftp, $USERNAME, $PASSWORD)) {
            throw new Exception('ログインに失敗しました。');
        }

        // turn on passive mode
        if(!ftp_pasv($this->ftp, true)){
            throw new Exception('PASVモードに設定できませんでした');
        }
        if(!ftp_set_option($this->ftp, FTP_USEPASVADDRESS, true)){
            throw new Exception('PASVモードオプションが設定できませんでした');
        }

        $this->ftpDownload();

        $upload_dirs = ['items/','shipping_schedule/','instock_schedule/','return_schedule/'];
        foreach ($upload_dirs as $upload_dir) {
            $this->ftpUpload($upload_dir);
        }
        ftp_close($this->ftp);
        echo "Successful.\n";
    }

    /**
     * @throws Exception
     */
    private function ftpDownload(): void
    {
        $download_dir_remote = "/OUT/";
        $download_dir_local = 'var/tmp/wms/receive/';

        //ファイル取得
        if(!ftp_chdir($this->ftp, $download_dir_remote)) {
            throw new Exception('/OUTディレクトリに移動できません。');
        }

        // create folder on local to save downloaded files if not exist
        if (!file_exists($download_dir_local) && !mkdir($download_dir_local, 0777, true)) {
            throw new Exception("ダウンロードフォルダが作成できません。");
        }

        $nlist = ftp_nlist($this->ftp, ".");
        if ($nlist === false) {
            throw new Exception('ファイル一覧が取得できません。');
        }

        foreach ($nlist as $file) {
            $localPath = $download_dir_local . $file;
            $remotePath = $download_dir_remote . $file;
            // download a file
            if (ftp_get($this->ftp, $localPath, $remotePath, FTP_BINARY)) {
                ftp_delete($this->ftp, $remotePath);
                echo "download success: from $remotePath to $localPath\n";
            } else {
                echo "download failed: from $remotePath to $localPath\n";
            }
        }
    }

    /**
     * @throws Exception
     */
    private function ftpUpload(string $directory, string $remoteDir = '/IN/'): void
    {
        $localDir = $this->tmpWmsDir . $directory;

        // create new folder on local to save moved files from old folder
        if (!file_exists($localDir) && !mkdir($localDir, 0777, true)) {
            throw new Exception("Can't create directory.");
        }

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

        if(!ftp_chdir($this->ftp, $remoteDir)) {
            throw new Exception('/INディレクトリに移動できません。');
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
            if (ftp_put($this->ftp, $remotePath, $localPath, FTP_ASCII)) {
                // delete local file
                //unlink($localPath);
                rename($localPath, $newLocalDir . $fileName); // move files
                echo "upload success: from $localPath to $remotePath\n";
            } else {
                echo "upload failed: from $localPath to $remotePath\n";
            }
        }
    }
}
