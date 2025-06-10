<?php

/*
 * Copyright(c) 2020 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Service;

use Eccube\Entity\Order;
use Eccube\Service\Payment\PaymentResult;
use Plugin\GmoPaymentGateway4\Service\Method\Ganb;
use Plugin\GmoPaymentGateway4\Util\PaymentUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Eccube\Common\EccubeConfig;

/**
 * 銀行振込（バーチャル口座 あおぞら） 決済処理を行うクラス
 */
class PaymentHelperGanb extends PaymentHelper
{
    public function __construct(
        EccubeConfig $eccubeConfig
    ) {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * GMO-PG 支払方法別のクラス名称を取得する
     *
     * @return string 支払方法別のクラス名称
     */
    protected function getGmoPaymentMethodClass()
    {
        return Ganb::class;
    }

    /**
     * 銀行振込（バーチャル口座 あおぞら）決済を行う
     *
     * @param Order $Order 注文
     * @param array $sendData 送信データ
     * @return boolean
     */
    public function doRequest(Order $Order, array $sendData)
    {
        PaymentUtil::logInfo('PaymentHelperGanb::doRequest start.');

        $const = $this->eccubeConfig;
        $action_status = 'gmo_payment_gateway.action_status.';
        $pay_status = 'gmo_payment_gateway.pay_status.';

        $url = $this->GmoConfig->getServerUrl() . 'EntryTranGANB.idPass';
        $paramNames = [
            'ShopID',
            'ShopPass',
            'OrderID',
            'Amount',
            'Tax',
        ];

        // 決済進捗
        $sendData['action_status'] = $const[$action_status . 'entry_request'];
        // 取引状態
        $sendData['pay_status'] = $const[$pay_status . 'unsettled'];
        $sendData['success_pay_status'] = '';
        $sendData['fail_pay_status'] = $const[$pay_status . 'fail'];

        $r = $this->sendOrderRequest($Order, $url, $paramNames, $sendData);
        if (!$r) {
            return $r;
        }

        $url = $this->GmoConfig->getServerUrl() . 'ExecTranGANB.idPass';
        $paramNames = [
            'ShopID',
            'ShopPass',
            'AccessID',
            'AccessPass',
            'OrderID',
            'ClientField1',
            'ClientField2',
            'ClientField3',
            'AccountHolderOptionalName',
            'TradeDays',
            'TradeReason',
            'TradeClientName',
            'TradeClientMailaddress',
        ];

        // 取引事由
        $sendData['TradeReason'] =
            PaymentUtil::subString($Order->getSummaryProductName(), 64);
        // 振込依頼人氏名
        $sendData['TradeClientName'] = $Order->getFullName();
        // 振込依頼人メールアドレス
        $sendData['TradeClientMailaddress'] = $Order->getEmail();

        // 決済進捗
        $sendData['action_status'] = $const[$action_status . 'exec_request'];
        // 取引状態
        $sendData['pay_status'] = '';
        $sendData['success_pay_status'] = $const[$pay_status . 'trading'];
        $sendData['fail_pay_status'] = $const[$pay_status . 'fail'];

        $r = $this->sendOrderRequest($Order, $url, $paramNames, $sendData);

        PaymentUtil::logInfo('PaymentHelperGanb::doRequest end.');
        
        return $r;
    }
}
