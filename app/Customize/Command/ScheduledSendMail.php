<?php

namespace Customize\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Customize\Service\BreederMailService;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\CustomerRepository;
use Customize\Repository\ScheduledMailRepository;

/**
 */
class ScheduledSendMail extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'eccube:customize:scheduled-mail';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var ScheduledMailRepository
     */
    protected $scheduledMailRepository;

    /**
     * @var BreederMailService
     */
    protected $breederMailService;

    
    public function __construct(
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        ScheduledMailRepository $scheduledMailRepository,
        BreederMailService $breederMailService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->scheduledMailRepository = $scheduledMailRepository;
        $this->breederMailService = $breederMailService;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Subscription order batch.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $today = (new \DateTime())->format("Y/m/d");

        $qb = $this->scheduledMailRepository->createQueryBuilder("m")
            ->where("m.send_date = '".$today."'")
            ->andWhere("m.execute_time is null");

        $mails = $qb->getQuery()->getResult();

        foreach($mails as $mail) {
            echo "CustomerId : ".$mail->getCustomer()->getId()."\n";
            echo "MailType : ".$mail->getMailType()."\n";

            if($mail->getMailType() == 1){
                echo "ブリーダー証明書更新依頼";
                $this->breederMailService->sendLicenseExpire($mail->getCustomer());

                $mail->setExecuteTime(new \DateTime());
                $this->entityManager->persist($mail);
            }
        }
        $this->entityManager->flush();

        echo "Subscription orders handled.\n";
    }
}
