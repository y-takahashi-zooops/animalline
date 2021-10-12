<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Customize\Service\SubscriptionProcess;

/**
 */
class BatchCommandExec extends Command
{
    protected static $defaultName = 'eccube:customize:batch-command-exec';

    /**
     * @var SubscriptionProcess
     */
    protected $subscriptionProcess;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(
        SubscriptionProcess $subscriptionProcess,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->subscriptionProcess = $subscriptionProcess;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // 定期購入次回配送日10日前処理
        $this->subscriptionProcess->nextDeliveryDateBefore10days();

        // 定期購入次回配送日7日前処理
        $this->subscriptionProcess->nextDeliveryDateBefore7Days();
    }
}
