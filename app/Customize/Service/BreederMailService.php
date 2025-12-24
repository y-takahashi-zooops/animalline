<?php

namespace Customize\Service;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Customize\Entity\Breeders;
use Eccube\Entity\MailHistory;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\MailHistoryRepository;
use Eccube\Repository\MailTemplateRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class BreederMailService
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var MailTemplateRepository
     */
    protected $mailTemplateRepository;

    /**
     * @var MailHistoryRepository
     */
    private $mailHistoryRepository;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

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
     * MailService constructor.
     *
     * @param MailerInterface $mailer
     * @param MailTemplateRepository $mailTemplateRepository
     * @param MailHistoryRepository $mailHistoryRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param Environment $twig
     * @param EccubeConfig $eccubeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        MailerInterface $mailer,
        MailTemplateRepository $mailTemplateRepository,
        MailHistoryRepository $mailHistoryRepository,
        BaseInfoRepository $baseInfoRepository,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        EccubeConfig $eccubeConfig,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->mailHistoryRepository = $mailHistoryRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    /**
     * Send customer confirm mail.
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendCustomerConfirmMail(\Customize\Entity\Breeders $Breeders, $activateUrl)
    {
        $this->logger->info('仮会員登録メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Breeders' => $Breeders,
            'BaseInfo' => $this->BaseInfo,
            'activateUrl' => $activateUrl,
        ]);

        $email = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Breeders->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'Breeders' => $Breeders,
        //         'BaseInfo' => $this->BaseInfo,
        //         'activateUrl' => $activateUrl,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'message' => $email,
                'Breeders' => $Breeders,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_CUSTOMER_CONFIRM);

        try {
            $this->mailer->send($email);
            $this->logger->info('仮会員登録メール送信完了', ['email' => $Breeders->getEmail()]);
            return 1;
        } catch (\Throwable $e) {
            log_error('メール送信エラー', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Send customer complete mail.
     *
     * @param $Customer 会員情報
     */
    public function sendCustomerCompleteMail(\Customize\Entity\Breeders $Breeders)
    {
        $this->logger->info('会員登録完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_complete_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Breeders' => $Breeders,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $email = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Breeders->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'Breeders' => $Breeders,
        //         'BaseInfo' => $this->BaseInfo,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'message' => $email,
                'Breeders' => $Breeders,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_CUSTOMER_COMPLETE);

        $this->mailer->send($email);

        $this->logger->info('会員登録完了メール送信完了');

        return 1;
    }

    /**
     * Send withdraw mail.
     *
     * @param $Breeders Breeders
     * @param $email string
     */
    public function sendCustomerWithdrawMail(Breeders $Breeders, string $email)
    {
        $this->logger->info('退会手続き完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_customer_withdraw_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Breeders' => $Breeders,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $emailMessage = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($email))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'Breeders' => $Breeders,
        //         'BaseInfo' => $this->BaseInfo,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'email' => $emailMessage,
                'Breeders' => $Breeders,
                'BaseInfo' => $this->BaseInfo,
                'email' => $email,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_CUSTOMER_WITHDRAW);

         // Send email via Symfony Mailer
        $this->mailer->send($emailMessage);

        $this->logger->info('退会手続き完了メール送信完了');

        return 1;
    }

    /**
     * Send contact mail.
     *
     * @param $formData お問い合わせ内容
     */
    public function sendContactMail($formData)
    {
        $this->logger->info('お問い合わせ受付メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_contact_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'data' => $formData,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // 問い合わせ者にメール送信
        $emailMessage = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail02(), $this->BaseInfo->getShopName()))
            ->to(new Address($formData['email']))
            ->bcc($this->BaseInfo->getEmail02())
            ->replyTo(new Address($this->BaseInfo->getEmail02()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'data' => $formData,
        //         'BaseInfo' => $this->BaseInfo,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'email' => $emailMessage,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_CONTACT);

        // メール送信
        $count = $this->mailer->send($emailMessage);

        $this->logger->info('お問い合わせ受付メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send order mail.
     *
     * @param \Eccube\Entity\Order $Order 受注情報
     *
     * @return Email
     */
    public function sendOrderMail(\Eccube\Entity\Order $Order)
    {
        $this->logger->info('受注メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_order_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Order' => $Order,
        ]);

        $emailMessage = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Order->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo(new Address($this->BaseInfo->getEmail03()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'Order' => $Order,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'message' => $emailMessage,
                'Order' => $Order,
                'MailTemplate' => $MailTemplate,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_ORDER);

        // メール送信
        $count = $this->mailer->send($emailMessage);

        // メール履歴の保存
        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($emailMessage->getSubject())
            ->setMailBody($emailMessage->getTextBody())
            ->setOrder($Order)
            ->setSendDate(new \DateTime());

        // HTML用メールの設定
        if ($emailMessage instanceof \Symfony\Component\Mime\Email) {
            if ($emailMessage->getHtmlBody()) {
                $MailHistory->setMailHtmlBody($emailMessage->getHtmlBody());
            }
        }

        $this->mailHistoryRepository->save($MailHistory);

        $this->logger->info('受注メール送信完了', ['count' => $count]);

        return $emailMessage;
    }

    /**
     * Send admin customer confirm mail.
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendAdminCustomerConfirmMail(\Customize\Entity\Breeders $Breeders, $activateUrl)
    {
        $this->logger->info('仮会員登録再送メール送信開始');

        /* @var $MailTemplate \Eccube\Entity\MailTemplate */
        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Breeders' => $Breeders,
            'activateUrl' => $activateUrl,
        ]);

        $emailMessage = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail03(), $this->BaseInfo->getShopName()))
            ->to(new Address($Breeders->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo(new Address($this->BaseInfo->getEmail03()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'BaseInfo' => $this->BaseInfo,
        //         'Breeders' => $Breeders,
        //         'activateUrl' => $activateUrl,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'message' => $emailMessage,
                'Breeders' => $Breeders,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_ADMIN_CUSTOMER_CONFIRM);

        // メール送信
        $count = $this->mailer->send($emailMessage);

        $this->logger->info('仮会員登録再送メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send admin order mail.
     *
     * @param Order $Order 受注情報
     * @param $formData 入力内容
     *
     * @return Email
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendAdminOrderMail(Order $Order, $formData)
    {
        $this->logger->info('受注管理通知メール送信開始');

        $emailMessage = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$formData['mail_subject'])
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Order->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo(new Address($this->BaseInfo->getEmail03()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($formData['tpl_data']);

        $event = new EventArgs(
            [
                'message' => $emailMessage,
                'Order' => $Order,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_ADMIN_ORDER);

        // メール送信
        $count = $this->mailer->send($emailMessage);

        $this->logger->info('受注管理通知メール送信完了', ['count' => $count]);

        return $emailMessage;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $reset_url
     */
    public function sendPasswordResetNotificationMail(\Customize\Entity\Breeders $Breeders, $reset_url)
    {
        $this->logger->info('パスワード再発行メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_forgot_mail_template_id']);
        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Breeders' => $Breeders,
            'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
            'reset_url' => $reset_url,
        ]);

        $emailMessage = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Breeders->getEmail()))
            ->replyTo(new Address($this->BaseInfo->getEmail03()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'BaseInfo' => $this->BaseInfo,
        //         'Breeders' => $Breeders,
        //         'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
        //         'reset_url' => $reset_url,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'message' => $emailMessage,
                'Breeders' => $Breeders,
                'BaseInfo' => $this->BaseInfo,
                'resetUrl' => $reset_url,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_PASSWORD_RESET);

        // メール送信
        $count = $this->mailer->send($emailMessage);

        $this->logger->info('パスワード再発行メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $password
     */
    public function sendPasswordResetCompleteMail(\Customize\Entity\Breeders $Breeders, $password)
    {
        $this->logger->info('パスワード変更完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_reset_complete_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Breeders' => $Breeders,
            'password' => $password,
        ]);

        $emailMessage = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Breeders->getEmail()))
            ->bcc(new Address($this->BaseInfo->getEmail01()))
            ->replyTo(new Address($this->BaseInfo->getEmail03()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->twig->render($htmlFileName, [
        //         'BaseInfo' => $this->BaseInfo,
        //         'Breeders' => $Breeders,
        //         'password' => $password,
        //     ]);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        $event = new EventArgs(
            [
                'message' => $emailMessage,
                'Breeders' => $Breeders,
                'BaseInfo' => $this->BaseInfo,
                'password' => $password,
            ],
            null
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::MAIL_PASSWORD_RESET_COMPLETE);

        // メール送信
        $count = $this->mailer->send($emailMessage);

        $this->logger->info('パスワード変更完了メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * 発送通知メールを送信する.
     * 発送通知メールは受注ごとに送られる
     *
     * @param Shipping $Shipping
     *
     * @throws \Twig_Error
     */
    public function sendShippingNotifyMail(Shipping $Shipping)
    {
        $this->logger->info('出荷通知メール送信処理開始', ['id' => $Shipping->getId()]);

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_shipping_notify_mail_template_id']);

        /** @var Order $Order */
        $Order = $Shipping->getOrder();
        $body = $this->getShippingNotifyMailBody($Shipping, $Order, $MailTemplate->getFileName());

        $emailMessage = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Order->getEmail()))
            ->bcc(new Address($this->BaseInfo->getEmail01()))
            ->replyTo(new Address($this->BaseInfo->getEmail03()))
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        // $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        // if (!is_null($htmlFileName)) {
        //     $htmlBody = $this->getShippingNotifyMailBody($Shipping, $Order, $htmlFileName, true);

        //     $message
        //         ->setContentType('text/plain; charset=UTF-8')
        //         ->setBody($body, 'text/plain')
        //         ->addPart($htmlBody, 'text/html');
        // } else {
            // $message->setBody($body);
        // }

        // メール送信
        $this->mailer->send($emailMessage);

        // メール履歴の保存
        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($emailMessage->getSubject())
            ->setMailBody($emailMessage->getBody())
            ->setOrder($Order)
            ->setSendDate(new \DateTime());

        // HTML用メールの設定
        if ($emailMessage instanceof \Symfony\Component\Mime\Email) {
            if ($emailMessage->getHtmlBody()) {
                $MailHistory->setMailHtmlBody($emailMessage->getHtmlBody());
            }
        }

        $this->mailHistoryRepository->save($MailHistory);

        $this->logger->info('出荷通知メール送信処理完了', ['id' => $Shipping->getId()]);
    }

    /**
     * @param Shipping $Shipping
     * @param Order $Order
     * @param string|null $templateName
     * @param boolean $is_html
     *
     * @return string
     *
     * @throws \Twig_Error
     */
    public function getShippingNotifyMailBody(Shipping $Shipping, Order $Order, $templateName = null, $is_html = false)
    {
        $ShippingItems = array_filter($Shipping->getOrderItems()->toArray(), function (OrderItem $OrderItem) use ($Order) {
            return $OrderItem->getOrderId() === $Order->getId();
        });

        if (is_null($templateName)) {
            /** @var MailTemplate $MailTemplate */
            $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_shipping_notify_mail_template_id']);
            $fileName = $MailTemplate->getFileName();
        } else {
            $fileName = $templateName;
        }

        if ($is_html) {
            $htmlFileName = $this->getHtmlTemplate($fileName);
            $fileName = !is_null($htmlFileName) ? $htmlFileName : $fileName;
        }

        return $this->twig->render($fileName, [
            'Shipping' => $Shipping,
            'ShippingItems' => $ShippingItems,
            'Order' => $Order,
        ]);
    }

    /**
     * [getHtmlTemplate description]
     *
     * @param  string $templateName  プレーンテキストメールのファイル名
     *
     * @return string|null  存在する場合はファイル名を返す
     */
    public function getHtmlTemplate($templateName)
    {
        // メールテンプレート名からHTMLメール用テンプレート名を生成
        $fileName = explode('.', $templateName);
        $suffix = '.html';
        $htmlFileName = $fileName[0].$suffix.'.'.$fileName[1];

        // HTMLメール用テンプレートの存在チェック
        if ($this->twig->getLoader()->exists($htmlFileName)) {
            return $htmlFileName;
        } else {
            return null;
        }
    }

    /**
     * 動物取扱者証明書更新通知
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendLicenseExpire($Customer)
    {
        $this->logger->info('受注メール送信開始');

        // メール内容作成
        $body = $this->twig->render('Mail/Breeder/breeder_license_expire.twig', [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $message = (new \Symfony\Component\Mime\Email())
            ->subject('【Animalline】動物取扱業登録証画像送付のお願い')
            ->from($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName())
            ->to($Customer->getEmail())
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body); 

        // メール送信
        $count = $this->mailer->send($message);

        $count = $this->mailer->send($message);

        return $count;
    }
}
