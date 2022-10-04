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
use Customize\Repository\BreedersRepository;
use Customize\Repository\ConservationsRepository;

class MailService
{
    /**
     * @var \Swift_Mailer
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
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * MailService constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param MailTemplateRepository $mailTemplateRepository
     * @param MailHistoryRepository $mailHistoryRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param \Twig_Environment $twig
     * @param EccubeConfig $eccubeConfig
     * @param BreedersRepository $breedersRepository
     * @param ConservationsRepository $conservationsRepository
     */
    public function __construct(
        \Swift_Mailer $mailer,
        MailTemplateRepository $mailTemplateRepository,
        MailHistoryRepository $mailHistoryRepository,
        BaseInfoRepository $baseInfoRepository,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        EccubeConfig $eccubeConfig,
        BreedersRepository $breedersRepository,
        ConservationsRepository $conservationsRepository
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->mailHistoryRepository = $mailHistoryRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
        $this->breedersRepository = $breedersRepository;
        $this->conservationsRepository = $conservationsRepository;
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

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
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
    public function sendCustomerCompleteMail(\Eccube\Entity\Customer $Customer,$prefix)
    {
        log_info('会員登録完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_complete_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
            "prefix" => $prefix,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                "prefix" => $prefix,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_COMPLETE, $event);

        $count = $this->mailer->send($message);

        log_info('会員登録完了メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send withdraw mail.
     *
     * @param $Conservations Conservation
     * @param $email string
     */
    public function sendCustomerWithdrawMail(Conservations $Conservation, string $email)
    {
        log_info('退会手続き完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_customer_withdraw_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Conservation' => $Conservation,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Conservation' => $Conservation,
                'BaseInfo' => $this->BaseInfo,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Conservation' => $Conservation,
                'BaseInfo' => $this->BaseInfo,
                'email' => $email,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_WITHDRAW, $event);

        $count = $this->mailer->send($message);

        log_info('退会手続き完了メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send contact mail.
     *
     * @param $formData お問い合わせ内容
     */
    public function sendContactMail($formData,$attachFile)
    {
        log_info('お問い合わせ受付メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_contact_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'data' => $formData,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // 問い合わせ者にメール送信
        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail02() => $this->BaseInfo->getShopName()])
            ->setTo([$formData['email']])
            ->setBcc($this->BaseInfo->getEmail02())
            ->setReplyTo($this->BaseInfo->getEmail02())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if($attachFile){
            $message->attach(\Swift_Attachment::fromPath("var/tmp/contact/".$attachFile)->setFilename($attachFile));
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'data' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CONTACT, $event);

        $count = $this->mailer->send($message);

        log_info('お問い合わせ受付メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send order mail.
     *
     * @param \Eccube\Entity\Order $Order 受注情報
     *
     * @return \Swift_Message
     */
    public function sendOrderMail(\Eccube\Entity\Order $Order)
    {
        log_info('受注メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_order_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Order' => $Order,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Order->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Order' => $Order,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Order' => $Order,
                'MailTemplate' => $MailTemplate,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ORDER, $event);

        $count = $this->mailer->send($message);

        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($message->getSubject())
            ->setMailBody($message->getBody())
            ->setOrder($Order)
            ->setSendDate(new \DateTime());

        // HTML用メールの設定
        $multipart = $message->getChildren();
        if (count($multipart) > 0) {
            $MailHistory->setMailHtmlBody($multipart[0]->getBody());
        }

        $this->mailHistoryRepository->save($MailHistory);

        log_info('受注メール送信完了', ['count' => $count]);

        return $message;
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

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'activateUrl' => $activateUrl,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail03() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'activateUrl' => $activateUrl,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
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
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ADMIN_CUSTOMER_CONFIRM, $event);

        $count = $this->mailer->send($message);

        log_info('仮会員登録再送メール送信完了', ['count' => $count]);

        return $count;
    }


    /**
     * Send admin order mail.
     *
     * @param Order $Order 受注情報
     * @param $formData 入力内容
     *
     * @return \Swift_Message
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendAdminOrderMail(Order $Order, $formData)
    {
        log_info('受注管理通知メール送信開始');

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$formData['mail_subject'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Order->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04())
            ->setBody($formData['tpl_data']);

        $event = new EventArgs(
            [
                'message' => $message,
                'Order' => $Order,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ADMIN_ORDER, $event);

        $count = $this->mailer->send($message);

        log_info('受注管理通知メール送信完了', ['count' => $count]);

        return $message;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $reset_url
     */
    public function sendPasswordResetNotificationMail(\Customize\Entity\Conservations $Conservation, $reset_url)
    {
        log_info('パスワード再発行メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_forgot_mail_template_id']);
        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Conservation' => $Conservation,
            'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
            'reset_url' => $reset_url,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Conservation->getEmail()])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Conservation' => $Conservation,
                'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
                'reset_url' => $reset_url,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Conservation' => $Conservation,
                'BaseInfo' => $this->BaseInfo,
                'resetUrl' => $reset_url,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_PASSWORD_RESET, $event);

        $count = $this->mailer->send($message);

        log_info('パスワード再発行メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $password
     */
    public function sendPasswordResetCompleteMail(\Customize\Entity\Conservations $Conservation, $password)
    {
        log_info('パスワード変更完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_reset_complete_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Conservation' => $Conservation,
            'password' => $password,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Conservation->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Conservation' => $Conservation,
                'password' => $password,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Conservation' => $Conservation,
                'BaseInfo' => $this->BaseInfo,
                'password' => $password,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_PASSWORD_RESET_COMPLETE, $event);

        $count = $this->mailer->send($message);

        log_info('パスワード変更完了メール送信完了', ['count' => $count]);

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

        /** @var Order $Order */
        $Order = $Shipping->getOrder();
        $body = $this->getShippingNotifyMailBody($Shipping, $Order, $MailTemplate->getFileName());

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo($Order->getEmail())
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->getShippingNotifyMailBody($Shipping, $Order, $htmlFileName, true);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $this->mailer->send($message);

        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($message->getSubject())
                ->setMailBody($message->getBody())
                ->setOrder($Order)
                ->setSendDate(new \DateTime());

        // HTML用メールの設定
        $multipart = $message->getChildren();
        if (count($multipart) > 0) {
            $MailHistory->setMailHtmlBody($multipart[0]->getBody());
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
        //HTMLテンプレートを無効にする
        return null;
        
        /*
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
        */
    }

    /**
     * Send breeder examination accept mail.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendBreederExaminationMailAccept(\Eccube\Entity\Customer $Customer, $data)
    {
        $body = $this->twig->render('Mail/breeder_examination_ok.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 審査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/breeder_examination_ok.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send breeder examination reject mail.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendBreederExaminationMailReject(\Eccube\Entity\Customer $Customer, $data)
    {
        $body = $this->twig->render('Mail/breeder_examination_ng.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 審査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/breeder_examination_ng.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send adoption examination accept mail.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendAdoptionExaminationMailAccept(\Eccube\Entity\Customer $Customer, $data)
    {
        $body = $this->twig->render('Mail/conservation_examination_ok.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 審査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/conservation_examination_ok.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send adoption examination reject mail.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendAdoptionExaminationMailReject(\Eccube\Entity\Customer $Customer, $data)
    {
        $body = $this->twig->render('Mail/conservation_examination_ng.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 審査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/conservation_examination_ng.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send pet public ok.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendPetPublicOk(\Eccube\Entity\Customer $Customer, $data)
    {
        $body = $this->twig->render('Mail/Pet/pet_public_ok.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] ＤＮＡ検査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/Pet/pet_public_ok.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send pet public ng.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendPetPublicNg(\Eccube\Entity\Customer $Customer, $data)
    {
        $body = $this->twig->render('Mail/Pet/pet_public_ng.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] ＤＮＡ検査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/Pet/pet_public_ng.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send mail notify message.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param \Customize\Entity\BreederContacts $data
     * @return int
     */
    public function sendMailNoticeMsg(\Eccube\Entity\Customer $Customer, $data)
    {
        if($data->getMessageFrom() == 1){
            $breeder = $this->breedersRepository->find($Customer->getId());
            $user_name = $breeder->getBreederName();
        }
        else{
            $user_name = $Customer->getName01()."　".$Customer->getName02();
        }

        $body = $this->twig->render('Mail/message_contact.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] メッセージ受信通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send cancel contract mail.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param array $data
     * @return int
     */
    public function sendMailContractCancelToShop(\Eccube\Entity\Customer $Customer, $msgheader, $site_type)
    {
        if($site_type == 1){
            $breeder = $this->breedersRepository->find($Customer->getId());
            $user_name = $breeder->getBreederName();
        }
        else{
            $conservation = $this->conservationsRepository->find($Customer->getId());
            $user_name = $conservation->getOrganizationName();
        }
           
        $body = $this->twig->render('Mail/mail_contract_cancel_to_shop.twig', [
            'BaseInfo' => $this->BaseInfo,
            'msgheader' => $msgheader,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 取引不成立通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send cancel contract mail.
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param array $data
     * @return int
     */
    public function sendMailContractCancelToUser(\Eccube\Entity\Customer $Customer, $msgheader, $site_type)
    {
        $user_name = $Customer->getName01()."　".$Customer->getName02();

        $body = $this->twig->render('Mail/mail_contract_cancel_to_user.twig', [
            'BaseInfo' => $this->BaseInfo,
            'msgheader' => $msgheader,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 取引不成立通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }


    /**
     * 交渉中→成約確認待ちメール（購入・譲渡者に送る）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param array $data
     * @return int
     */
    public function sendMailContractCheckToUser(\Eccube\Entity\Customer $Customer, $msgheader, $site_type)
    {
        $user_name = $Customer->getName01()."　".$Customer->getName02();

        $pet = $msgheader->getPet();
        $body = $this->twig->render('Mail/mail_contract_check_to_user.twig', [
            'BaseInfo' => $this->BaseInfo,
            'msgheader' => $msgheader,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 取引成立通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * 交渉中→成約確認待ちメール（ブリーダー・保護団体に送る）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param array $data
     * @return int
     */
    public function sendMailContractCheckToShop(\Eccube\Entity\Customer $Customer, $msgheader, $site_type)
    {
        if($site_type == 1){
            $breeder = $this->breedersRepository->find($Customer->getId());
            $user_name = $breeder->getBreederName();
        }
        else{
            $conservation = $this->conservationsRepository->find($Customer->getId());
            $user_name = $conservation->getOrganizationName();
        }

        $pet = $msgheader->getPet();
        $body = $this->twig->render('Mail/mail_contract_check_to_shop.twig', [
            'BaseInfo' => $this->BaseInfo,
            'msgheader' => $msgheader,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 取引成立通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * 成約確認待ち→成約（購入・譲渡者に送る）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param array $data
     * @return int
     */
    public function sendMailContractCompleteToUser(\Eccube\Entity\Customer $Customer, $msgheader, $site_type)
    {
        $user_name = $Customer->getName01()."　".$Customer->getName02();

        $body = $this->twig->render('Mail/mail_contract_complete_to_user.twig', [
            'BaseInfo' => $this->BaseInfo,
            'msgheader' => $msgheader,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 取引完了通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * 成約確認待ち→成約（購入・譲渡者に送る）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param array $data
     * @return int
     */
    public function sendMailContractCompleteToShop(\Eccube\Entity\Customer $Customer, $msgheader, $site_type)
    {
        if($site_type == 1){
            $breeder = $this->breedersRepository->find($Customer->getId());
            $user_name = $breeder->getBreederName();
        }
        else{
            $conservation = $this->conservationsRepository->find($Customer->getId());
            $user_name = $conservation->getOrganizationName();
        }

        $body = $this->twig->render('Mail/mail_contract_complete_to_shop.twig', [
            'BaseInfo' => $this->BaseInfo,
            'msgheader' => $msgheader,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 取引完了通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    
    /**
     * 保護団体お問い合わせ受付（フロントページ・マイページ）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param int $data
     * @return int
     */
    public function sendMailContractReply(\Eccube\Entity\Customer $Customer, $data)
    {
        if($data->getMessageFrom() == 1){
            $conservation = $this->conservationsRepository->find($Customer->getId());
            $user_name = $conservation->getOrganizationName();
        }
        else{
            $user_name = $Customer->getName01()."　".$Customer->getName02();
        }

        $body = $this->twig->render('Mail/mail_contract_reply.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] メッセージ受信通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/mail_contract_reply.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * 保護団体お問い合わせ受付（フロントページ・マイページ）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param int $data
     * @return int
     */
    public function sendMailContractAccept(\Eccube\Entity\Customer $Customer, $site_type)
    {
        if($site_type == 1){
            $breeder = $this->breedersRepository->find($Customer->getId());
            $user_name = $breeder->getBreederName();
        }
        else{
            $conservation = $this->conservationsRepository->find($Customer->getId());
            $user_name = $conservation->getOrganizationName();
        }

        $body = $this->twig->render('Mail/mail_contract_accept.twig', [
            'BaseInfo' => $this->BaseInfo,
            'site_type' => $site_type,
            'UserName' => $user_name
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] メッセージ受信通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate('Mail/mail_contract_reply.twig');
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'data' => $data
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        return $this->mailer->send($message, $failures);
    }

    /**
     * DNA検査結果NGの際の通知メール送信
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendDnaCheckNg(\Eccube\Entity\Customer $Customer,$Dna,$restext)
    {
        $body = $this->twig->render('Mail/dna_check_ng.twig', [
            'BaseInfo' => $this->BaseInfo,
            'name' => $Customer->getName01()." ".$Customer->getName02(),
            'dna_id' => $Dna->getSiteType() . sprintf('%05d', $Dna->getId()),
            'restext' => $restext
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 検査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * DNA検査結果NGの際の通知メール送信
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendDnaCheckOk(\Eccube\Entity\Customer $Customer,$Dna,$ResultText)
    {
        $body = $this->twig->render('Mail/dna_check_ok.twig', [
            'BaseInfo' => $this->BaseInfo,
            'name' => $Customer->getName01()." ".$Customer->getName02(),
            'dna_id' => $Dna->getSiteType() . sprintf('%05d', $Dna->getId()),
            'result_text' => $ResultText
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 検査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * DNA検査結果NGの際の通知メール送信
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendDnaCheckRetry(\Eccube\Entity\Customer $Customer,$Dna)
    {
        $body = $this->twig->render('Mail/dna_check_retry.twig', [
            'BaseInfo' => $this->BaseInfo,
            'name' => $Customer->getName01()." ".$Customer->getName02(),
            'dna_id' => $Dna->getSiteType() . sprintf('%05d', $Dna->getId())
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] 検査結果通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * ＶＥＱＴＡ検査結果通知メール送信
     *
     * @param \Eccube\Entity\Customer $Customer
     * @param $data
     * @return int
     */
    public function sendVeqtaResuletToAdmin($data)
    {
        $body = $this->twig->render('Mail/veqta_result_to_admin.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] DNA検査完了通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$this->BaseInfo->getEmail01()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * ＶＥＱＴＡ検査結果通知メール送信
     *
     * @param $data
     * @return int
     */
    public function sendDnaKitSendComplete($email, $data)
    {
        $body = $this->twig->render('Mail/dna_kit_send_complete.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] DNA検査キット発送完了通知')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * Breeder remind pet.
     *
     * @param $email
     * @param $data
     * @return int
     */
    public function sendBreederRemindPet($email, $data)
    {
        $body = $this->twig->render('Mail/Breeder/breeder_remind_pet.twig', [
            'BaseInfo' => $this->BaseInfo,
            'data' => $data
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 最終のペット登録日からの時間経過')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * リマインドメール（会員登録後ブリーダー未申請）
     *
     * @param \Eccube\Entity\Customer $Customer
     * @return int
     */
    public function sendBreederRemindRegist(\Eccube\Entity\Customer $Customer)
    {
        $body = $this->twig->render('Mail/Breeder/breeder_remind_regist.twig', [
            'BaseInfo' => $this->BaseInfo,
            'customer' => $Customer
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] ブリーダー情報登録のお願い')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * リマインドメール（DNAキット未請求）
     *
     * @param $email
     * @param $data
     * @return int
     */
    public function sendBreederRemindDna(\Eccube\Entity\Customer $Customer)
    {
        $body = $this->twig->render('Mail/Breeder/breeder_remind_dna.twig', [
            'BaseInfo' => $this->BaseInfo,
            'customer' => $Customer
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] DNA検査のお願い')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * Send mail notify to admin when a seminar is registered.
     *
     * @param array $registeredData
     *
     * @return int
     */
    public function sendNotifyReceiveSeminarRegistered(array $registeredData): int
    {
        $body = $this->twig->render('Mail/Seminar/receive_registered.twig', [
            'BaseInfo' => $this->BaseInfo,
            'registeredData' => $registeredData
        ]);
        $message = (new \Swift_Message())
            ->setSubject('オンラインセミナーへの参加申し込み')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04())
            ->setBody($body, 'text/html');

        return $this->mailer->send($message);
    }

    /**
     * ブリーダー一斉メール
     *
     * @param \Eccube\Entity\Customer $Customer
     * @return int
     */
    public function sendAllBreederMail(\Eccube\Entity\Customer $Customer)
    {
        $body = $this->twig->render('Mail/Breeder/breeder_all.twig', [
            'BaseInfo' => $this->BaseInfo,
            'customer' => $Customer
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 秋のキャンペーンのご案内')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04())
            ->attach(\Swift_Attachment::fromPath('/var/www/animalline/var/campaign20220830_all.pdf')->setFilename('秋のキャンペーンのご案内.pdf')->setContentType('application/pdf'));
        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

    /**
     * ブリーダー一斉メール
     *
     * @param \Eccube\Entity\Customer $Customer
     * @return int
     */
    public function sendAllBreederMail2(\Eccube\Entity\Customer $Customer)
    {
        $body = $this->twig->render('Mail/Breeder/breeder_all.twig', [
            'BaseInfo' => $this->BaseInfo,
            'customer' => $Customer
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[' . $this->BaseInfo->getShopName() . '] 秋のキャンペーンのご案内')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04())
            ->attach(\Swift_Attachment::fromPath('/var/www/animalline/var/campaign20220830_register.pdf')->setFilename('秋のキャンペーンのご案内.pdf')->setContentType('application/pdf'));
        $message->setBody($body);

        return $this->mailer->send($message, $failures);
    }

}
