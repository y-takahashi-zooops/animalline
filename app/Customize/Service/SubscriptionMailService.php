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

use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;

class SubscriptionMailService
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * SubscriptionMailService constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param BaseInfoRepository $baseInfoRepository
     * @param \Twig_Environment $twig
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        \Swift_Mailer $mailer,
        BaseInfoRepository $baseInfoRepository,
        \Twig_Environment $twig,
        EccubeConfig $eccubeConfig
    ) {
        $this->mailer = $mailer;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
    }

     /**
     * 次回お届け日変更リマインドメール送信
     *
     * @param $Order
     * @param $Shipping 
     * @param $SubscriptionContract 
     */
    public function sendSubscriotionRemindMail($Customer)
    {
        log_info('次回お届け日変更リマインドメール送信開始');

        // メール内容作成
        $body = $this->twig->render('Mail/subscription_remind.twig', [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // タイトル、宛名等を作成
        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] ' . '定期注文次回お届け日変更期限のお知らせ')
            ->setFrom([$this->BaseInfo->getEmail02() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail02())
            ->setReplyTo($this->BaseInfo->getEmail02())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        // $htmlFileName = 'Mail/subscription_remind.html.twig';
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'Customer' => $Customer,
        //         'BaseInfo' => $this->BaseInfo,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            $message->setBody($body);
        // }

        $count = $this->mailer->send($message);

        log_info('次回お届け日変更リマインドメール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * 次回お届け日確定メール送信
     *
     * @param $Order
     * @param $Shipping 
     * @param $SubscriptionContract 
     */
    public function sendSubscriotionConfirmMail($Order, $Shipping, $SubscriptionContract)
    {
        log_info('次回お届け日確定メール送信開始');

        // メール内容作成
        $body = $this->twig->render('Mail/subscription_confirm.twig', [
            'Order' => $Order,
            'Shipping' => $Shipping,
            'data' => $SubscriptionContract,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // タイトル、宛名等を作成
        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] ' . '定期注文お届け日確定のお知らせ')
            ->setFrom([$this->BaseInfo->getEmail02() => $this->BaseInfo->getShopName()])
            ->setTo([$Order->getEmail()])
            ->setBcc($this->BaseInfo->getEmail02())
            ->setReplyTo($this->BaseInfo->getEmail02())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        // $htmlFileName = 'Mail/subscription_confirm.html.twig';
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'Order' => $Order,
        //         'Shipping' => $Shipping,
        //         'data' => $SubscriptionContract,
        //         'BaseInfo' => $this->BaseInfo,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            $message->setBody($body);
        // }

        $count = $this->mailer->send($message);

        log_info('次回お届け日確定メール送信完了', ['count' => $count]);

        return $count;
    }
}
