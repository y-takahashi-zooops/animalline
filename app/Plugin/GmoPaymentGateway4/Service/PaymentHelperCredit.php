<?php

/*
 * Copyright(c) 2018 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Service;

use Eccube\Entity\Order;
use Eccube\Service\Payment\PaymentResult;
use Plugin\GmoPaymentGateway4\Service\Method\CreditCard;
use Plugin\GmoPaymentGateway4\Util\PaymentUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\PaymentRepository;
use Eccube\Service\MailService;
use Eccube\Service\PluginService;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Plugin\GmoPaymentGateway4\Repository\GmoConfigRepository;
use Plugin\GmoPaymentGateway4\Repository\GmoOrderPaymentRepository;
use Plugin\GmoPaymentGateway4\Repository\GmoPaymentMethodRepository;
use Plugin\GmoPaymentGateway4\Repository\GmoMemberRepository;
use Plugin\GmoPaymentGateway4\Util\ErrorUtil;

/**
 * クレジット決済処理を行うクラス
 */
class PaymentHelperCredit extends PaymentHelper
{
    public function __construct(
        ContainerInterface $container,
        MailerInterface $mailer,
        Environment $twig,
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        PluginService $pluginService,
        OrderRepository $orderRepository,
        OrderStatusRepository $orderStatusRepository,
        BaseInfoRepository $baseInfoRepository,
        CustomerRepository $customerRepository,
        PaymentRepository $paymentRepository,
        MailService $mailService,
        PurchaseFlow $shoppingPurchaseFlow,
        GmoConfigRepository $gmoConfigRepository,
        GmoOrderPaymentRepository $gmoOrderPaymentRepository,
        GmoPaymentMethodRepository $gmoPaymentMethodRepository,
        GmoMemberRepository $gmoMemberRepository,
        ErrorUtil $errorUtil
    ) {
        parent::__construct(
            $container,
            $mailer,
            $twig,
            $eccubeConfig,
            $entityManager,
            $pluginService,
            $orderRepository,
            $orderStatusRepository,
            $baseInfoRepository,
            $customerRepository,
            $paymentRepository,
            $mailService,
            $shoppingPurchaseFlow,
            $gmoConfigRepository,
            $gmoOrderPaymentRepository,
            $gmoPaymentMethodRepository,
            $gmoMemberRepository,
            $errorUtil
        );
    }

    /**
     * GMO-PG 支払方法別のクラス名称を取得する
     *
     * @return string 支払方法別のクラス名称
     */
    protected function getGmoPaymentMethodClass()
    {
        $className = CreditCard::class;

        // 不正検知機能を初期化
        $this->fraudDetector->initPaymentMethodClass($className);

        return $className;
    }

    /**
     * クレジットカード決済を行う
     *
     * @param Order $Order 注文
     * @param array $sendData 送信データ
     * @return boolean
     */
    public function doRequest(Order $Order, array $sendData)
    {
        PaymentUtil::logInfo('PaymentHelperCredit::doRequest start.');

        $const = $this->eccubeConfig;

        $url = $this->GmoConfig->getServerUrl() . 'EntryTran.idPass';
        $paramNames = [
            'ShopID',
            'ShopPass',
            'OrderID',
            'JobCd',
            'Amount',
            'TdFlag',
            'TdTenantName',
        ];

        // データ補正
        if ($sendData['payment_type'] === "0") {
            // クレジットカード入力決済
            if (isset($sendData['CardSeq'])) {
                unset($sendData['CardSeq']);
            }
        }

        // 3DS1.0,3DS2.0の場合
        if ((isset($sendData['TdFlag']) && !empty($sendData['TdFlag'])) ||
            (!isset($sendData['TdFlag']) &&
             !empty($this->gmoPaymentMethodConfig['TdFlag']))) {
            $paramNames[] = 'TdRequired';
        }

        $sendData['action_status'] =
            $const['gmo_payment_gateway.action_status.entry_request'];
        $sendData['pay_status'] =
            $const['gmo_payment_gateway.pay_status.unsettled'];
        $sendData['success_pay_status'] = '';
        $sendData['fail_pay_status'] =
            $const['gmo_payment_gateway.pay_status.fail'];

        $GmoOrderPayment = $Order->getGmoOrderPayment();
        if (!$GmoOrderPayment->isExistsAccessIDAndPass()) {
            $r = $this->sendOrderRequest($Order, $url, $paramNames, $sendData);
            if (!$r) {
                $this->fraudDetector->errorOccur();
                return $r;
            }
        }

        $url = $this->GmoConfig->getServerUrl() . 'ExecTran.idPass';
        $paramNames = [
            'AccessID',
            'AccessPass',
            'OrderID',
            'Method',
            'PayTimes',
            'ClientField1',
            'ClientField2',
            'ClientField3',
        ];

        $paramNames[] = 'HttpAccept';
        $paramNames[] = 'HttpUserAgent';
        $paramNames[] = 'DeviceCategory';

        // パラメータ補正
        if ($sendData['payment_type'] === "0") {
            // クレジットカード入力決済
            $paramNames[] = 'Token';
            // データ補正
            $sendData['Method'] = $sendData['credit_pay_methods'];
        } else {
            // 登録済みクレジットカード決済
            $paramNames = array_merge($paramNames, [
                'SiteID',
                'SitePass',
                'MemberID',
                'CardSeq',
                'ClientFieldFlag',
                'SeqMode',
            ]);
            // データ補正
            $sendData['Method'] = $sendData['credit_pay_methods2'];
        }

        // 3DS2.0の場合
        if ((isset($sendData['TdFlag']) && $sendData['TdFlag'] == '2') ||
            (!isset($sendData['TdFlag']) &&
             $this->gmoPaymentMethodConfig['TdFlag'] == '2')) {
            $paramNames = array_merge($paramNames, [
                'RetUrl',

                'Tds2ChAccChange',
                'Tds2ChAccDate',
                'Tds2ShipNameInd',
                'Tds2BillAddrCountry',
                'Tds2BillAddrLine1',
                'Tds2BillAddrLine2',
                'Tds2BillAddrPostCode',
                'Tds2BillAddrState',
                'Tds2Email',
                'Tds2ShipAddrCountry',
                'Tds2ShipAddrLine1',
                'Tds2ShipAddrLine2',
                'Tds2ShipAddrPostCode',
                'Tds2ShipAddrState',
            ]);

            $sendData['RetUrl'] = $this->container->get('router')
                ->generate('gmo_payment_gateway_3dsecure', ['version' => 2],
                           UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $sendData['action_status'] =
            $const['gmo_payment_gateway.action_status.exec_request'];
        $sendData['pay_status'] = '';
        $sendData['success_pay_status'] =
            $const['gmo_payment_gateway.pay_status.auth'];
        if (!empty($sendData['JobCd'])) {
            $status = 'gmo_payment_gateway.pay_status.' .
                strtolower($sendData['JobCd']);
            $sendData['success_pay_status'] = $const[$status];
        } else if (!is_null($this->gmoPaymentMethodConfig['JobCd'])) {
            $status = 'gmo_payment_gateway.pay_status.' .
                strtolower($this->gmoPaymentMethodConfig['JobCd']);
            $sendData['success_pay_status'] = $const[$status];
        }
        $sendData['fail_pay_status'] =
            $const['gmo_payment_gateway.pay_status.fail'];
        if (isset($sendData['TdFlag'])) {
            if ($sendData['TdFlag'] == '1' || $sendData['TdFlag'] == '2') {
                $sendData['success_pay_status'] =
                    $const['gmo_payment_gateway.pay_status.unsettled'];
            }
        } else if ($this->gmoPaymentMethodConfig['TdFlag'] == '1' ||
                   $this->gmoPaymentMethodConfig['TdFlag'] == '2') {
            $sendData['success_pay_status'] =
                $const['gmo_payment_gateway.pay_status.unsettled'];
        }

        $r = $this->sendOrderRequest($Order, $url, $paramNames, $sendData);

        if (!$r) {
            $this->fraudDetector->errorOccur();
        }

        PaymentUtil::logInfo('PaymentHelperCredit::doRequest end.');
        
        return $r;
    }

    /**
     * 3Dセキュアを利用する設定か否かを返す
     *
     * @param array $sendData 送信データ
     * @return boolean true: 利用する, false: 利用しない
     */
    protected function use3dSecure(array $sendData)
    {
        ;
    }

    /**
     * 決済実行の結果3Dセキュアを実行するためのレスポンスが返ってきたか確認する
     *
     * @return boolean true: 3Dセキュア, false: 非3Dセキュア
     */
    public function is3dSecureResponse()
    {
        $results = $this->getResults();
        if (!empty($results['ACS']) && $results['ACS'] === "1" &&
            !empty($results['ACSUrl']) &&
            !empty($results['PaReq']) &&
            !empty($results['MD'])) {
            return true;
        }

        return false;
    }

    /**
     * 決済実行の結果3Dセキュア2.0を実行するためのレスポンスが
     * 返ってきたか確認する
     *
     * @return boolean true: 3Dセキュア2.0, false: 非3Dセキュア2.0
     */
    public function is3dSecure2Response()
    {
        $results = $this->getResults();
        if (!empty($results['ACS']) && $results['ACS'] === "2" &&
            !empty($results['RedirectUrl'])) {
            return true;
        }

        return false;
    }

    /**
     * 3Dセキュアパスワード入力画面へリダイレクトする情報を返す
     *
     * @return PaymentResult
     */
    public function redirectTo3dSecurePage()
    {
        PaymentUtil::logInfo
            ('PaymentHelperCredit::redirectTo3dSecurePage start.');

        $sendData = [];
        $results = $this->getResults();

        $sendData['ACSUrl'] = $results['ACSUrl'];
        $sendData['PaReq'] = $results['PaReq'];
        $sendData['TermUrl'] = $this->router->generate(
            'gmo_payment_gateway_3dsecure', [], UrlGeneratorInterface::ABSOLUTE_URL
        );
        $sendData['MD'] = $results['MD'];

        $template = '@GmoPaymentGateway4/payments/credit_3dsecure.twig';
        $contents = $this->twig->render($template, ['sendData' => $sendData]);

        $result = new PaymentResult();
        $result->setSuccess(true);
        $result->setResponse(Response::create($contents));

        PaymentUtil::logInfo
            ('PaymentHelperCredit::redirectTo3dSecurePage end.');

        return $result;
    }

    /**
     * 3Dセキュア2.0パスワード入力画面へリダイレクトする情報を返す
     *
     * @return PaymentResult
     */
    public function redirectTo3dSecure2Page()
    {
        PaymentUtil::logInfo
            ('PaymentHelperCredit::redirectTo3dSecure2Page start.');

        $sendData = [];
        $results = $this->getResults();

        $sendData['RedirectUrl'] = $results['RedirectUrl'];

        $template = '@GmoPaymentGateway4/payments/credit_3dsecure2.twig';
        $contents = $this->twig->render($template, ['sendData' => $sendData]);

        $result = new PaymentResult();
        $result->setSuccess(true);
        $result->setResponse(Response::create($contents));

        PaymentUtil::logInfo
            ('PaymentHelperCredit::redirectTo3dSecure2Page end.');

        return $result;
    }

    /**
     * 本人認証サービス（3Dセキュア）パスワード入力画面後の処理
     * ReceiveController で利用する
     *
     * @param Order $Order 受注
     * @param array $receiveData 受信データ
     * @return boolean true: OK, false: NG
     */
    public function do3dSecureContinuation(Order $Order, array $receiveData)
    {
        PaymentUtil::logInfo
            ('PaymentHelperCredit::do3dSecureContinuation start.');

        $const = $this->eccubeConfig;

        // 取引ID(MD)の検証
        $paymentLogData = $Order->getGmoOrderPayment()->getPaymentLogData();
        if (isset($paymentLogData['MD']) &&
            $paymentLogData['MD'] !== $receiveData['MD']) {
            $msg = 'gmo_payment_gateway.shopping.credit.3dsecure.error1';
            $this->setError(trans($msg, [
                '%MD1%' => $receiveData['MD'],
                '%MD2%' => $paymentLogData['MD'],
            ]));
            return false;
        }

        // 結果電文の検証
        if (empty($receiveData['PaRes'])) {
            $msg = 'gmo_payment_gateway.shopping.credit.3dsecure.error2';
            $this->setError(trans($msg));
            return false;
        }

        $url = $this->GmoConfig->getServerUrl() . 'SecureTran.idPass';

        $paramNames = [
            'PaRes',
            'MD',
        ];

        $receiveData['action_status'] =
            $const['gmo_payment_gateway.action_status.recv_notice'];
        $receiveData['success_pay_status'] =
            $const['gmo_payment_gateway.pay_status.auth'];
        if (!is_null($this->gmoPaymentMethodConfig['JobCd'])) {
            $status = 'gmo_payment_gateway.pay_status.' .
                strtolower($this->gmoPaymentMethodConfig['JobCd']);
            $receiveData['success_pay_status'] = $const[$status];
        }
        $receiveData['fail_pay_status'] =
            $const['gmo_payment_gateway.pay_status.fail'];

        $r = $this->sendOrderRequest($Order, $url, $paramNames, $receiveData);

        if (!$r) {
            $this->fraudDetector->errorOccur();
        }

        PaymentUtil::logInfo
            ('PaymentHelperCredit::do3dSecureContinuation end.');

        return $r;
    }

    /**
     * 本人認証サービス（3Dセキュア2.0）パスワード入力画面後の処理
     * ReceiveController で利用する
     *
     * @param Order $Order 受注
     * @param array $receiveData 受信データ
     * @return boolean true: OK, false: NG
     */
    public function do3dSecure2Continuation(Order $Order, array $receiveData)
    {
        PaymentUtil::logInfo
            ('PaymentHelperCredit::do3dSecure2Continuation start.');

        $const = $this->eccubeConfig;

        // 取引ID(AccessID)の検証
        $paymentLogData = $Order->getGmoOrderPayment()->getPaymentLogData();
        if (isset($paymentLogData['AccessID']) &&
            $paymentLogData['AccessID'] !== $receiveData['AccessID']) {
            $msg = 'gmo_payment_gateway.shopping.credit.3dsecure2.error1';
            $this->setError(trans($msg, [
                '%AccessID1%' => $receiveData['AccessID'],
                '%AccessID2%' => $paymentLogData['AccessID'],
            ]));
            return false;
        }

        $url = $this->GmoConfig->getServerUrl() . 'SecureTran2.idPass';

        $paramNames = [
            'AccessID',
            'AccessPass',
        ];

        $receiveData['action_status'] =
            $const['gmo_payment_gateway.action_status.recv_notice'];
        $receiveData['success_pay_status'] =
            $const['gmo_payment_gateway.pay_status.auth'];
        if (!is_null($this->gmoPaymentMethodConfig['JobCd'])) {
            $status = 'gmo_payment_gateway.pay_status.' .
                strtolower($this->gmoPaymentMethodConfig['JobCd']);
            $receiveData['success_pay_status'] = $const[$status];
        }
        $receiveData['fail_pay_status'] =
            $const['gmo_payment_gateway.pay_status.fail'];

        $r = $this->sendOrderRequest($Order, $url, $paramNames, $receiveData);

        if (!$r) {
            $this->fraudDetector->errorOccur();
        }

        PaymentUtil::logInfo
            ('PaymentHelperCredit::do3dSecure2Continuation end.');

        return $r;
    }

    /**
     * 注文後に OrderID を利用してカード登録を行う
     *
     * @param Order $Order 注文
     * @param array $sendData 送信データ
     */
    public function doRegistCard(Order $Order, array $sendData)
    {
        PaymentUtil::logInfo('PaymentHelperCredit::doRegistCard start.');

        $url = $this->GmoConfig->getServerUrl() . 'TradedCard.idPass';

        $paramNames = [
            'ShopID',
            'ShopPass',
            'OrderID',
            'SiteID',
            'SitePass',
            'MemberID',
            'SeqMode',
            'DefaultFlag',
            'HolderName',
        ];

        // データ補正
        $GmoOrderPayment = $Order->getGmoOrderPayment();
        $sendData['OrderID'] = $GmoOrderPayment->getGmoOrderID();
        if (isset($sendData['CardSeq'])) {
            unset($sendData['CardSeq']);
        }

        $data = $this->getIfSendData($paramNames, $sendData, $Order);

        $r = $this->sendRequest($url, $data);
        if ($r) {
            // カード登録連番（物理）を保存
            $results = $this->getResults();
            $GmoOrderPayment->setCardSeq($results['CardSeq']);
            $this->entityManager->persist($GmoOrderPayment);
            $this->entityManager->flush();
        }

        PaymentUtil::logInfo('PaymentHelperCredit::doRegistCard end.');

        return $r;
    }

    /**
     * クレジットカード編集機能が利用可能かどうかを返す
     *
     * @return boolean true: 可, false: 不可
     */
    public function isAvailableCardEdit()
    {
        // カード登録機能の有効化有無を確認
        if (!$this->GmoConfig->getCardRegistFlg()) {
            return false;
        }

        // 支払方法を確認する
        $Payment = $this->paymentRepository
            ->findOneBy(['method_class' => CreditCard::class]);
        if (is_null($Payment)) {
            return false;
        }

        return true;
    }

    /**
     * [オーバーライド] 決済毎に購入完了画面およびメールに
     * 表示する内容を生成する
     *
     * @param Order $Order
     * @return array 表示データ配列
     */
    protected function makeOrderCompleteMessages(Order $Order)
    {
        $data = [];
        $results = $this->getResults();
        $prefix = "gmo_payment_gateway.payment_helper.";

        // 承認番号
        if (isset($results['Approve']) && !is_null($results['Approve'])) {
            $data['Approve']['name'] = trans($prefix . 'approve');
            $data['Approve']['value'] = $results['Approve'];
        }

        // 決済完了案内メール
        if (isset($this->gmoPaymentMethodConfig['order_mail_title1']) &&
            isset($this->gmoPaymentMethodConfig['order_mail_body1'])) {
            $data['order_mail_title1']['name'] =
                $this->gmoPaymentMethodConfig['order_mail_title1'];
            $data['order_mail_title1']['value'] =
                $this->gmoPaymentMethodConfig['order_mail_body1'];
        }

        return $data;
    }
}
