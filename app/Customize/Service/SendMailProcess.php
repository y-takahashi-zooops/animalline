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
use Plugin\ZooopsSendmail\Repository\MailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Customize\Service\ShippingMailService;

class SendMailProcess
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var ShippingMailService
     */
    protected $shippingMailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerRepository $customerRepository,
        MailTemplateRepository $mailTemplateRepository,
        ShippingMailService $shippingMailService
    ) {
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->shippingMailService = $shippingMailService;
    }

    /**
     * 一斉メール送信用CSV出力
     */
    public function csvExport($searchData, $template_id)
    {
        $template = $this->mailTemplateRepository->find($template_id);
        $title = $template->getTemplateTitle();
        $body = $template->getTemplateDetail();

        $qb = $this->customerRepository->getSearchData($searchData);

        //データ行挿入
        foreach ($qb as $customer) {
            $this->shippingMailService->sendPlaneMail($title,$body,$customer);
        }
    }

    /**
     * テンプレート登録
     */
    public function registTemplate($data)
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush($data);
    }

    /**
     * テンプレート削除
     */
    public function removeTemplate($data)
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush($data);
    }
}
