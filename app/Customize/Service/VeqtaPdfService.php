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

use Customize\Config\AnilineConf;
use Eccube\Common\EccubeConfig;
use Exception;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\TcpdfFpdi;

/**
 * Class VeqtaPdfService.
 * Do export pdf function.
 */
class VeqtaPdfService extends TcpdfFpdi
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /** ダウンロードするPDFファイルのデフォルト名 */
    const DEFAULT_PDF_FILE_NAME = 'dna_check.pdf';
    /** FONT ゴシック */
    const FONT_GOTHIC = 'kozgopromedium';
    /** FONT 明朝 */
    const FONT_SJIS = 'kozminproregular';

    // lfTextのoffset
    private $baseOffsetX = 0;
    private $baseOffsetY = -4;

    /** 発行日 @var string */
    private $issueDate = '';

    /**
     * OrderPdfService constructor.
     * @param EccubeConfig $eccubeConfig
     * @throws Exception
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
        parent::__construct();

        $this->widthCell = [110.3, 12, 21.7, 24.5];

        $this->SetFont(self::FONT_SJIS);

        $this->SetMargins(15, 20);

        $this->setPrintHeader(false);

        $this->setPrintFooter(true);
        $this->setFooterMargin();
        $this->setFooterFont([self::FONT_SJIS, '', 8]);
    }

    /**
     * Create pdf file
     *
     * @param array $data
     * @return bool
     * @throws PdfParserException
     */
    public function makePdf(array $data): bool
    {
        $userPath = $this->eccubeConfig->get('eccube_theme_app_dir') . '/pdf/dna_check.pdf';
        $this->setSourceFile($userPath);

        // PDFにページを追加する
        $this->addPdfPage();

        $this->renderBreederName($data['breeder_name']);

        $this->renderPetData($data['pet']);

        if($data['result'] == 6){
            $this->lfText(20, 230, "上記、検査項目において検出する遺伝子変異が原因となる発症リスクはありません。", 10);
        }
        if($data['result'] == 4){
            $this->lfText(20, 230, "検体異常です", 10);
        }
        if($data['result'] == 7){
            $this->lfText(20, 230, "上記、検査項目において検出する遺伝子変異が原因となる発症リスクがございます。", 10);
        }

        $this->renderCheckKinds($data['check_kinds']);

        return true;
    }

    /**
     * フッターに発行日を出力する.
     */
    public function Footer()
    {
        $this->Cell(0, 0, $this->issueDate, 0, 0, 'R');
    }

    /**
     * 作成するPDFのテンプレートファイルを指定する.
     */
    protected function addPdfPage()
    {
        $this->AddPage();

        $tplIdx = $this->importPage(1);

        $this->useTemplate($tplIdx, null, null, null, null, true);
    }

    /**
     * Render Breeder data
     *
     * @param $data
     * @return void
     */
    protected function renderBreederName($data)
    {
        $this->lfText(85, 107, $data, 10);
    }

    /**
     * Render Pet data
     *
     * @param $data
     * @return void
     */
    protected function renderPetData($data)
    {
        $this->lfText(85, 120, $data->getBreedsType()->getBreedsName(), 10);
        //$this->lfText(80, 140, '不要', 10);
        $this->lfText(85, 133, $data->getPetBirthday()->format('Y年m月d日'), 10);
        $this->lfText(85, 146, $data->getPetSex() == 1 ? '男の子' : '女の子', 10);

        //$this->lfText(80, 172, $data->getPedigreeCode(), 10);
        //$this->lfText(80, 183, $data->getMicrochipCode(), 10);
    }

    /**
     * メッセージを設定する.
     *
     * @param array $rows
     * @return bool
     */
    protected function renderCheckKinds(array $rows): bool
    {
        $fromX = 20;
        $fromY = 164;
        // 検査結果
        $CHECK_RESULTS = [
            1 => 'クリア',
            2 => 'キャリア',
            3 => 'アフェクテッド'
        ];

        foreach ($rows as $row) {
            $this->lfTextWhite($fromX, $fromY, $row['check_kind_name'], 10);
            $this->lfText($fromX + 81, $fromY, $CHECK_RESULTS[$row['check_kind_result']] ?? '', 10);
            $fromY += 17;
            /*
            if (strlen($row['check_kind_name']) > 32) {
                $this->lfText($fromX, $fromY, substr($row['check_kind_name'], 0, 32), 8);
                $this->lfText($fromX, $fromY + 4, substr($row['check_kind_name'], 32), 8);
            } else {
                $this->lfText($fromX, $fromY, $row['check_kind_name'], 8);
            }
            $this->lfText($fromX + 92, $fromY + 2, $CHECK_RESULTS[$row['check_kind_result']] ?? '', 8);
            $fromY += 18;
            */
        }

        return true;
    }

    /**
     * PDFへのテキスト書き込み
     *
     * @param int $x X座標
     * @param int $y Y座標
     * @param string $text テキスト
     * @param int $size フォントサイズ
     * @param string $style フォントスタイル
     */
    protected function lfText($x, $y, $text, $size = 0, $style = '')
    {
        // 退避
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->SetTextColor(0,0,0);
        $this->Text($x + $this->baseOffsetX, $y + $this->baseOffsetY, $text);

        // 復元
        $this->SetFont('', $bakFontStyle, $bakFontSize);
    }

    /**
     * PDFへのテキスト書き込み
     *
     * @param int $x X座標
     * @param int $y Y座標
     * @param string $text テキスト
     * @param int $size フォントサイズ
     * @param string $style フォントスタイル
     */
    protected function lfTextWhite($x, $y, $text, $size = 0, $style = '')
    {
        // 退避
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->SetTextColor(255,255,255);
        $this->Text($x + $this->baseOffsetX, $y + $this->baseOffsetY, $text);

        // 復元
        $this->SetFont('', $bakFontStyle, $bakFontSize);
    }
}
