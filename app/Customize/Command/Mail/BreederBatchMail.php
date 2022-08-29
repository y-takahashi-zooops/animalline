<?php

namespace Customize\Command\Mail;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Service\MailService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\CustomerStatusRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BreederBatchMail extends Command
{
    protected static $defaultName = 'eccube:customize:breeder-batch-mail';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerStatusRepository
     */
    protected $customerStatusRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * Breeder remind mail constructor.
     * 
     * @param EntityManagerInterface $entityManager
     * @param BreedersRepository $breedersRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param CustomerRepository $customerRepository
     * @param CustomerStatusRepository $customerStatusRepository
     * @param MailService $mailService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BreedersRepository $breedersRepository,
        BreederPetsRepository $breederPetsRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        CustomerRepository $customerRepository,
        CustomerStatusRepository $customerStatusRepository,
        MailService $mailService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->customerRepository = $customerRepository;
        $this->customerStatusRepository = $customerStatusRepository;
        $this->mailService = $mailService;
    }

    protected function configure()
    {
        $this->setDescription('Breeder remind mail.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = $this->customerStatusRepository->find(2);

        $breeders = $this->breedersRepository->findBy(['is_active' => 1]);

        $lists = [252,253,255,256,259,261,263,265,267,268,272,275,276,277,279,280,281,284,285,286,270,288,164,292,296,299,249,301,302,303,304,174,305,310,260,314,293,319,320,312,322,309,313,323,325,326,334,335,336,338,329,340,341,343];
        foreach ($breeders as $breeder) {
            $customer = $this->customerRepository->findOneBy(['id' => $breeder->getId(),'Status' => $status]);

            if(!$customer){
                echo "ブリーダー無効:".$breeder->getBreederName()."\n";
            }
            else{
                if(in_array($customer->getId(),$lists)){
                    echo "対象メール送信：".$customer->getEmail()."(".$customer->getId().")".$breeder->getBreederName()."\n";

                    if(!$this->mailService->sendAllBreederMail2($customer)){
                        echo "メール送信失敗\n";
                    }
                }
                else{
                    echo "非対象メール送信：".$customer->getEmail()."(".$customer->getId().")".$breeder->getBreederName()."\n";

                    if(!$this->mailService->sendAllBreederMail($customer)){
                        echo "メール送信失敗\n";
                    }
                }
                

                /*
                if(!$this->mailService->sendAllBreederMail($customer)){
                    echo "メール送信失敗\n";
                }
                */
                sleep(1);
            }
        }
        
        echo "Breeder remind mail handled.\n";
    }
}
