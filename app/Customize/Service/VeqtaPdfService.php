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

    /** 購入詳細情報 ラベル配列
     * @var array
     */
    private $labelCell = [];

    /** 最後に処理した注文番号 @var string */
    private $lastOrderId = null;

    // --------------------------------------
    // Font情報のバックアップデータ
    /** @var string フォント名 */
    private $bakFontFamily;
    /** @var string フォントスタイル */
    private $bakFontStyle;
    /** @var string フォントサイズ */
    private $bakFontSize;
    // --------------------------------------

    // lfTextのoffset
    private $baseOffsetX = 0;
    private $baseOffsetY = -4;

    /** ダウンロードファイル名 @var string */
    private $downloadFileName = null;

    /** 発行日 @var string */
    private $issueDate = '';

    /**
     * OrderPdfService constructor.
     * @param EccubeConfig $eccubeConfig
     * @throws \Exception
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
        parent::__construct();

        // 購入詳細情報の設定を行う
        // 動的に入れ替えることはない
        $this->labelCell[] = '商品名 / 商品コード';
        $this->labelCell[] = '数量';
        $this->labelCell[] = '単価';
        $this->labelCell[] = '金額(税込)';
        $this->widthCell = [110.3, 12, 21.7, 24.5];

        // Fontの設定しておかないと文字化けを起こす
        $this->SetFont(self::FONT_SJIS);

        // PDFの余白(上左右)を設定
        $this->SetMargins(15, 20);

        // ヘッダーの出力を無効化
        $this->setPrintHeader(false);

        // フッターの出力を無効化
        $this->setPrintFooter(true);
        $this->setFooterMargin();
        $this->setFooterFont([self::FONT_SJIS, '', 8]);
    }

    public function makePdf(array $data)
    {
        $userPath = $this->eccubeConfig->get('eccube_theme_app_dir') . '/pdf/dna_check.pdf';
        $this->setSourceFile($userPath);

        // PDFにページを追加する
        $this->addPdfPage();

        $this->renderBreederName($data['breeder_name']);

        $this->renderPetData($data['pet']);

        $this->renderCheckKinds($data['check_kinds']);

        return true;
    }

    /**
     * メッセージを設定する.
     *
     * @param array $rows
     * @return bool
     */
    protected function renderCheckKinds(array $rows): bool
    {
        $fromX = 36;
        $fromY = 202;
        // 検査結果
        $CHECK_RESULTS = [
            AnilineConf::DNA_CHECK_RESULT_1 => 'クリア',
            AnilineConf::DNA_CHECK_RESULT_2 => 'キャリア',
            AnilineConf::DNA_CHECK_RESULT_3 => 'アフェクテッド'
        ];

        foreach ($rows as $row) {
            if (strlen($row['check_kind_name']) > 32) {
                $this->lfText($fromX, $fromY, substr($row['check_kind_name'], 0, 32), 8);
                $this->lfText($fromX, $fromY + 4, substr($row['check_kind_name'], 32), 8);
                $this->lfText($fromX + 92, $fromY + 2, $CHECK_RESULTS[$row['check_kind_result']] ?? '', 8);
            } else {
                $this->lfText($fromX, $fromY, $row['check_kind_name'], 8);
                $this->lfText($fromX + 92, $fromY + 2, $CHECK_RESULTS[$row['check_kind_result']] ?? '', 8);
            }
            $fromY += 18;
        }

        return true;
    }

    /**
     * PDFファイルを出力する.
     *
     * @return string|mixed
     */
    public function outputPdf()
    {
        return $this->Output($this->getPdfFileName(), 'S');
    }

    /**
     * PDFファイル名を取得する
     * PDFが1枚の時は注文番号をファイル名につける.
     *
     * @return string ファイル名
     */
    public function getPdfFileName()
    {
        if (!is_null($this->downloadFileName)) {
            return $this->downloadFileName;
        }
        $this->downloadFileName = self::DEFAULT_PDF_FILE_NAME;
        if ($this->PageNo() == 1) {
            $this->downloadFileName = 'nouhinsyo-No' . $this->lastOrderId . '.pdf';
        }

        return $this->downloadFileName;
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
        // ページを追加
        $this->AddPage();

        // テンプレートに使うテンプレートファイルのページ番号を取得
        $tplIdx = $this->importPage(1);

        // テンプレートに使うテンプレートファイルのページ番号を指定
        $this->useTemplate($tplIdx, null, null, null, null, true);
    }

    protected function renderBreederName($data)
    {
        $this->lfText(30, 117, $data, 12);
    }

    protected function renderPetData($data)
    {
        $this->lfText(80, 130, $data->getBreedsType()->getBreedsName(), 10);
        $this->lfText(80, 140, '不要', 10);
        $this->lfText(80, 151, $data->getPetBirthday()->format('Y年m月d日'), 10);
        $this->lfText(80, 162, $data->getPetSex() == 1 ? '男の子' : '女の子', 10);
        $this->lfText(80, 172, $data->getPedigreeCode(), 10);
        $this->lfText(80, 183, $data->getMicrochipCode(), 10);
    }

    /**
     * PDFに備考を設定数.
     *
     * @param array $formData
     */
    protected function renderEtcData(array $formData)
    {
        // フォント情報のバックアップ
        $this->backupFont();

        $this->Cell(0, 10, '', 0, 1, 'C', 0, '');

        $this->SetFont(self::FONT_GOTHIC, 'B', 9);
        $this->MultiCell(0, 6, '＜ 備考 ＞', 'T', 2, 'L', 0, '');

        $this->SetFont(self::FONT_SJIS, '', 8);

        $this->Ln();
        // rtrimを行う
        $text = preg_replace('/\s+$/us', '', $formData['note1'] . "\n" . $formData['note2'] . "\n" . $formData['note3']);
        $this->MultiCell(0, 4, $text, '', 2, 'L', 0, '');

        // フォント情報の復元
        $this->restoreFont();
    }

    /**
     * タイトルをPDFに描画する.
     *
     * @param string $title
     */
    protected function renderTitle($title)
    {
        // 基準座標を設定する
        $this->setBasePosition();

        // フォント情報のバックアップ
        $this->backupFont();

        //文書タイトル（納品書・請求書）
        $this->SetFont(self::FONT_GOTHIC, '', 15);
        $this->Cell(0, 10, $title, 0, 2, 'C', 0, '');
        $this->Cell(0, 66, '', 0, 2, 'R', 0, '');
        $this->Cell(5, 0, '', 0, 0, 'R', 0, '');

        // フォント情報の復元
        $this->restoreFont();
    }

    /**
     * PDFへのテキスト書き込み
     *
     * @param int    $x     X座標
     * @param int    $y     Y座標
     * @param string $text  テキスト
     * @param int    $size  フォントサイズ
     * @param string $style フォントスタイル
     */
    protected function lfText($x, $y, $text, $size = 0, $style = '')
    {
        // 退避
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->Text($x + $this->baseOffsetX, $y + $this->baseOffsetY, $text);

        // 復元
        $this->SetFont('', $bakFontStyle, $bakFontSize);
    }

    /**
     * Colored table.
     *
     * TODO: 後の列の高さが大きい場合、表示が乱れる。
     *
     * @param array $header 出力するラベル名一覧
     * @param array $data   出力するデータ
     * @param array $w      出力するセル幅一覧
     */
    protected function setFancyTable($header, $data, $w)
    {
        // フォント情報のバックアップ
        $this->backupFont();

        // 開始座標の設定
        $this->setBasePosition(0, 149);

        // Colors, line width and bold font
        $this->SetFillColor(216, 216, 216);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont(self::FONT_SJIS, 'B', 8);
        $this->SetFont('', 'B');

        // Header
        $this->Cell(5, 7, '', 0, 0, '', 0, '');
        $count = count($header);
        for ($i = 0; $i < $count; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();

        // Color and font restoration
        $this->SetFillColor(235, 235, 235);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;
        $h = 4;
        foreach ($data as $row) {
            // 行のの処理
            $i = 0;
            $h = 4;
            $this->Cell(5, $h, '', 0, 0, '', 0, '');

            // Cellの高さを保持
            $cellHeight = 0;
            foreach ($row as $col) {
                // 列の処理
                // TODO: 汎用的ではない処理。この指定は呼び出し元で行うようにしたい。
                // テキストの整列を指定する
                $align = ($i == 0) ? 'L' : 'R';

                // セル高さが最大値を保持する
                if ($h >= $cellHeight) {
                    $cellHeight = $h;
                }

                // 最終列の場合は次の行へ移動
                // (0: 右へ移動(既定)/1: 次の行へ移動/2: 下へ移動)
                $ln = ($i == (count($row) - 1)) ? 1 : 0;

                $this->MultiCell(
                    $w[$i], // セル幅
                    $cellHeight, // セルの最小の高さ
                    $col, // 文字列
                    1, // 境界線の描画方法を指定
                    $align, // テキストの整列
                    $fill, // 背景の塗つぶし指定
                    $ln                 // 出力後のカーソルの移動方法
                );
                $h = $this->getLastH();

                ++$i;
            }
            $fill = !$fill;
        }
        $this->Cell(5, $h, '', 0, 0, '', 0, '');
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->SetFillColor(255);

        // フォント情報の復元
        $this->restoreFont();
    }

    /**
     * 基準座標を設定する.
     *
     * @param int $x
     * @param int $y
     */
    protected function setBasePosition($x = null, $y = null)
    {
        // 現在のマージンを取得する
        $result = $this->getMargins();

        // 基準座標を指定する
        $actualX = is_null($x) ? $result['left'] : $x;
        $this->SetX($actualX);
        $actualY = is_null($y) ? $result['top'] : $y;
        $this->SetY($actualY);
    }

    /**
     * データが設定されていない場合にデフォルト値を設定する.
     *
     * @param array $formData
     */
    protected function setDefaultData(array &$formData)
    {
        $defaultList = [
            'title' => trans('admin.order.delivery_note_title__default'),
            'message1' => trans('admin.order.delivery_note_message__default1'),
            'message2' => trans('admin.order.delivery_note_message__default2'),
            'message3' => trans('admin.order.delivery_note_message__default3'),
        ];

        foreach ($defaultList as $key => $value) {
            if (is_null($formData[$key])) {
                $formData[$key] = $value;
            }
        }
    }

    /**
     * Font情報のバックアップ.
     */
    protected function backupFont()
    {
        // フォント情報のバックアップ
        $this->bakFontFamily = $this->FontFamily;
        $this->bakFontStyle = $this->FontStyle;
        $this->bakFontSize = $this->FontSizePt;
    }

    /**
     * Font情報の復元.
     */
    protected function restoreFont()
    {
        $this->SetFont($this->bakFontFamily, $this->bakFontStyle, $this->bakFontSize);
    }
}
