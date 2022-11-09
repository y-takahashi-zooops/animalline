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

        //$lists = [281,261,192,253,126,69,199,99,272,215,112,187,335,255,70,319,111,172,116,167,260,124,50,127,212,228,138,141,164,168,122,161,285,101,160,186,276,275,174,190,173,286,146,133,340,343,351,359,368,372,410,388,404,391,417,233,75,277,193,175,115,371];
        $lists = [37,45,69,116,124,160,115,153,181,112,187,186,193,106,157,138,170,218,100,107,146,222,175,229,101,102,238,130,202,133,235,195,263,277,275,279,164,299,301,302,296,249,286,293,319,260,48,322,267,276,312,320,336,341,340,326,255,323,236,366,371,330,384,404,417,206,190,292,388,429,428,385,270,430,159,314,368];
        foreach ($breeders as $breeder) {
            $customer = $this->customerRepository->findOneBy(['id' => $breeder->getId(),'Status' => $status]);

            if(!$customer){
                echo "ブリーダー無効:".$breeder->getBreederName()."\n";
            }
            else{
                //if(in_array($customer->getId(),$lists)){
                    echo "対象メール送信：".$customer->getEmail()."(".$customer->getId().")".$breeder->getBreederName()."\n";

                    if(!$this->mailService->sendAllBreederMail2($customer)){
                        echo "メール送信失敗\n";
                    }
                //}
                //else{
                    echo "メール送信しない：".$customer->getEmail()."(".$customer->getId().")".$breeder->getBreederName()."\n";
                    //echo "非対象メール送信：".$customer->getEmail()."(".$customer->getId().")".$breeder->getBreederName()."\n";
                    /*
                    if(!$this->mailService->sendAllBreederMail($customer)){
                        echo "メール送信失敗\n";
                    }
                    */
                //}
                

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
