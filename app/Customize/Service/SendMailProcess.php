<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Service;

use Eccube\Repository\CustomerRepository;
use Customize\Service\MailService;

class SendMailProcess
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    public function __construct(
        CustomerRepository $customerRepository,
        MailService $mailService
    ) {
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
    }

    /**
     * 一斉メール送信用CSV出力
     */
    public function csvExport($searchData,$template_title,$template_detail,$attach_file)
    {
        // ファイル名作成
        // $directorypath = 'var/tmp/mail/';
        // $now = new \DateTime();
        // $filename = 'mail_' . $now->format('YmdHis') . '.csv';
        // $csvpath = $directorypath . $filename;

        // フォルダがなければ作成する
        //if (!file_exists($directorypath)) {
        //    mkdir($directorypath, 0777, true);
        //}

        // ファイル名に従ってCSVファイル作成
        //touch($csvpath);

        // CSVファイルを開き編集
        //$createCsvFile = fopen($csvpath, "w");

        $qb = $this->customerRepository->getSearchData($searchData);

        //データ行挿入
        foreach ($qb as $customer) {
            $name = $customer->getName01()."　".$customer->getName02();
            $email = $customer->getEmail();

            $this->mailService->sendEmailForManyUser($email,$template_title,$template_detail,$name,$attach_file);
            /*
            $data[] = [
                'name' => $customer->getName01() . $customer->getName02(), // フルネーム
                'Email' => $customer->getEmail() // メールアドレス
            ];
            */
        }
        /*
        if ($createCsvFile) {
            foreach ($data as $line) {
                fputcsv($createCsvFile, $line);
            }
        }
        */

        // CSVファイルを閉じる
        //fclose($createCsvFile);

        /*
        // パラメータ設定
        $password = '';
        $method   = 'aes-256-cbc';
        $options  = OPENSSL_RAW_DATA;
        $iv       = openssl_random_pseudo_bytes(16);

        // 暗号化
        $enc_data = openssl_encrypt(
            file_get_contents($csvpath),
            $method,
            $password,
            $options,
            $iv
        );
        */

        // 出力
        //file_put_contents($csvpath, $enc_data);

        // // 複合化
        // $dec_data = openssl_decrypt(
        //     file_get_contents($csvpath),
        //     $method,
        //     $password,
        //     $options,
        //     $iv
        // );

        // file_put_contents($csvpath, $dec_data);

    }
}
