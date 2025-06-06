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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class SubscriptionMailService
{
    /**
     * @var MailerInterface
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
     * @var Environment
     */
    protected $twig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SubscriptionMailService constructor.
     *
     * @param MailerInterface $mailer
     * @param BaseInfoRepository $baseInfoRepository
     * @param Environment $twig
     * @param EccubeConfig $eccubeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        MailerInterface $mailer,
        BaseInfoRepository $baseInfoRepository,
        Environment $twig,
        EccubeConfig $eccubeConfig,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
        $this->logger = $logger;
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
        $this->logger->info('次回お届け日変更リマインドメール送信開始');

        // メール内容作成
        $body = $this->twig->render('Mail/subscription_remind.twig', [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // タイトル、宛名等を作成
        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . '定期注文次回お届け日変更期限のお知らせ')
            ->from($this->BaseInfo->getEmail02())
            ->to($Customer->getEmail())
            ->bcc($this->BaseInfo->getEmail02())
            ->replyTo($this->BaseInfo->getEmail02())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

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
            // $message->setBody($body);
        // }

        $this->mailer->send($email);

        $this->logger->info('次回お届け日変更リマインドメール送信完了');

        return 1;
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
        $this->logger->info('次回お届け日確定メール送信開始');

        // メール内容作成
        $body = $this->twig->render('Mail/subscription_confirm.twig', [
            'Order' => $Order,
            'Shipping' => $Shipping,
            'data' => $SubscriptionContract,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // タイトル、宛名等を作成
        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . '定期注文お届け日確定のお知らせ')
            ->from($this->BaseInfo->getEmail02())
            ->to($Order->getEmail())
            ->bcc($this->BaseInfo->getEmail02())
            ->replyTo($this->BaseInfo->getEmail02())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

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
            // $message->setBody($body);
        // }

        $this->mailer->send($email);

        $this->logger->info('次回お届け日確定メール送信完了');

        return 1;
    }
}
