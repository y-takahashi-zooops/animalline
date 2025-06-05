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

namespace Eccube\Service;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class MailService
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
     * MailService constructor.
     *
     * @param MailerInterface $mailer,
     * @param MailTemplateRepository $mailTemplateRepository
     * @param MailHistoryRepository $mailHistoryRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param Environment $twig
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        MailerInterface $mailer,
        MailTemplateRepository $mailTemplateRepository,
        MailHistoryRepository $mailHistoryRepository,
        BaseInfoRepository $baseInfoRepository,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        EccubeConfig $eccubeConfig
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->mailHistoryRepository = $mailHistoryRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
    }

    /**
     * Send customer confirm mail.
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendCustomerConfirmMail(\Eccube\Entity\Customer $Customer, $activateUrl)
    {
        log_info('仮会員登録メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
            'activateUrl' => $activateUrl,
        ]);

        $email = (new Email())
            ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Customer->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ]);
            $email
                ->text($body)
                ->html($htmlBody);
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_CONFIRM, $event);

        $count = $this->mailer->send($message, $failures);

        log_info('仮会員登録メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send customer complete mail.
     *
     * @param $Customer 会員情報
     */
    public function sendCustomerCompleteMail(\Eccube\Entity\Customer $Customer)
    {
        log_info('会員登録完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_complete_mail_template_id']);

        if (is_null($MailTemplate)) {
            log_error('会員登録完了メールテンプレートが見つかりません');
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Customer->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_COMPLETE, $event);

        try {
            $this->mailer->send($email);
            log_info('会員登録完了メール送信完了');
        } catch (\Throwable $e) {
            log_error('会員登録完了メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

        return true;
    }

    /**
     * Send withdraw mail.
     *
     * @param $Customer Customer
     * @param $email string
     */
    public function sendCustomerWithdrawMail(Customer $Customer)
    {
        log_info('退会手続き完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->findOneBy([
            'template_name' => '退会完了メール',
        ]);

        if (is_null($MailTemplate)) {
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $email = (new Email())
                ->subject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
                ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
                ->to(new Address($Customer->getEmail()))
                ->bcc($this->BaseInfo->getEmail01())
                ->replyTo($this->BaseInfo->getEmail03())
                ->returnPath($this->BaseInfo->getEmail04())
                ->text($body);


        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'email' => $email,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_WITHDRAW, $event);

        try {
            $this->mailer->send($email);
            log_info('退会手続き完了メール送信完了');
        } catch (\Throwable $e) {
            log_error('退会手続き完了メール送信失敗', ['error' => $e->getMessage()]);
        }

        return $email;
    }

    /**
     * Send contact mail.
     *
     * @param $formData お問い合わせ内容
     */
    public function sendContactMail($formData)
    {
        log_info('お問い合わせ受付メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find(
            $this->eccubeConfig['eccube_contact_mail_template_id']
        );

        if (is_null($MailTemplate)) {
            log_error('お問い合わせ受付メールテンプレートが見つかりません');
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'data' => $formData,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // 問い合わせ者にメール送信
        $email = (new Email())
                ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
                ->from(new Address($this->BaseInfo->getEmail02(), $this->BaseInfo->getShopName()))
                ->to(new Address($formData['email'])) // ここでフォームデータのメールアドレスを使用
                ->bcc($this->BaseInfo->getEmail02())
                ->replyTo($this->BaseInfo->getEmail02())
                ->returnPath($this->BaseInfo->getEmail04())
                ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'data' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CONTACT, $event);

        // メール送信
        try {
            $this->mailer->send($email);
            log_info('お問い合わせ受付メール送信完了');
        } catch (\Throwable $e) {
            log_error('お問い合わせ受付メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

        return true;
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
        log_info('受注メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_order_mail_template_id']);
        if (is_null($MailTemplate)) {
            log_error('受注メールテンプレートが見つかりません');
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Order' => $Order,
        ]);
        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Order->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Order' => $Order,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'Order' => $Order,
                'MailTemplate' => $MailTemplate,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ORDER, $event);

        // メール送信処理
        try {
            $count = $this->mailer->send($email);
            log_info('受注メール送信完了', ['count' => $count]);
        } catch (\Throwable $e) {
            log_error('受注メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($email->getSubject())
            ->setMailBody($email->getTextBody())
            ->setOrder($Order)
            ->setSendDate(new \DateTime());

        // HTML用メールの設定
        if ($email->getHtmlBody()) {
            $MailHistory->setMailHtmlBody($email->getHtmlBody());
        }

        $this->mailHistoryRepository->save($MailHistory);

        log_info('受注メール送信完了', ['count' => $count]);

        return true;
    }

    /**
     * Send admin customer confirm mail.
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendAdminCustomerConfirmMail(\Eccube\Entity\Customer $Customer, $activateUrl)
    {
        log_info('仮会員登録再送メール送信開始');

        /* @var $MailTemplate \Eccube\Entity\MailTemplate */
        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);
        if (is_null($MailTemplate)) {
            log_error('仮会員登録確認メールテンプレートが見つかりません');
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'activateUrl' => $activateUrl,
        ]);

        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail03(), $this->BaseInfo->getShopName()))
            ->to(new Address($Customer->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'activateUrl' => $activateUrl,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ADMIN_CUSTOMER_CONFIRM, $event);

        try {
            $count = $this->mailer->send($email);
            log_info('仮会員登録再送メール送信完了', ['count' => $count]);
        } catch (\Throwable $e) {
            log_error('仮会員登録再送メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

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
        log_info('受注管理通知メール送信開始');

        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $formData['mail_subject'])
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Order->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($formData['tpl_data']);

        $event = new EventArgs(
            [
                'message' => $email,
                'Order' => $Order,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ADMIN_ORDER, $event);

        try {
            $count = $this->mailer->send($email);
            log_info('受注管理通知メール送信完了', ['count' => $count]);
        } catch (\Throwable $e) {
            log_error('受注管理通知メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

        return $email;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $reset_url
     */
    public function sendPasswordResetNotificationMail(\Eccube\Entity\Customer $Customer, $reset_url)
    {
        log_info('パスワード再発行メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_forgot_mail_template_id']);
        if (is_null($MailTemplate)) {
            log_error('パスワード再発行メールテンプレートが見つかりません');
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
            'reset_url' => $reset_url,
        ]);

        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Customer->getEmail()))
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());

        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
                'reset_url' => $reset_url,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'resetUrl' => $reset_url,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_PASSWORD_RESET, $event);

        try {
            $count = $this->mailer->send($email);
            log_info('パスワード再発行メール送信完了', ['count' => $count]);
        } catch (\Throwable $e) {
            log_error('パスワード再発行メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

        return $count;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $password
     */
    public function sendPasswordResetCompleteMail(\Eccube\Entity\Customer $Customer, $password)
    {
        log_info('パスワード変更完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_reset_complete_mail_template_id']);
        if (is_null($MailTemplate)) {
            log_error('パスワード変更完了メールテンプレートが見つかりません');
            return null;
        }

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'password' => $password,
        ]);

        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Customer->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'password' => $password,
            ]);
            $email->html($htmlBody);
        }

        $event = new EventArgs(
            [
                'message' => $email,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'password' => $password,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_PASSWORD_RESET_COMPLETE, $event);

        try {
            $count = $this->mailer->send($email);
            log_info('パスワード変更完了メール送信完了', ['count' => $count]);
        } catch (\Throwable $e) {
            log_error('パスワード変更完了メール送信失敗', ['error' => $e->getMessage()]);
            return false;
        }

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
        log_info('出荷通知メール送信処理開始', ['id' => $Shipping->getId()]);

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_shipping_notify_mail_template_id']);
        if (is_null($MailTemplate)) {
            log_error('出荷通知メールテンプレートが見つかりません');
            return null;
        }

        /** @var Order $Order */
        $Order = $Shipping->getOrder();
        $body = $this->getShippingNotifyMailBody($Shipping, $Order, $MailTemplate->getFileName());

        $email = (new Email())
            ->subject('[' . $this->BaseInfo->getShopName() . '] ' . $MailTemplate->getMailSubject())
            ->from(new Address($this->BaseInfo->getEmail01(), $this->BaseInfo->getShopName()))
            ->to(new Address($Order->getEmail()))
            ->bcc($this->BaseInfo->getEmail01())
            ->replyTo($this->BaseInfo->getEmail03())
            ->returnPath($this->BaseInfo->getEmail04())
            ->text($body);

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        $htmlBody = null;
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->getShippingNotifyMailBody($Shipping, $Order, $htmlFileName, true);
            $email->html($htmlBody);
        }

        try {
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            log_error('出荷通知メール送信失敗', ['id' => $Shipping->getId(), 'error' => $e->getMessage()]);
            return;
        }

        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($message->getSubject())
                ->setMailBody($message->getBody())
                ->setOrder($Order)
                ->setSendDate(new \DateTime());

        // HTML用メールの設定
        if (!is_null($htmlBody)) {
            $MailHistory->setMailHtmlBody($htmlBody);
        }

        $this->mailHistoryRepository->save($MailHistory);

        log_info('出荷通知メール送信処理完了', ['id' => $Shipping->getId()]);
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
}
