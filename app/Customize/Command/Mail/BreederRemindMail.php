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

class BreederRemindMail extends Command
{
    protected static $defaultName = 'eccube:customize:breeder-remind-mail';

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

        $customers = $this->customerRepository->findBy(['regist_type' => 1,'Status' => $status]);

        $base_time = new DateTime("-3days");
        echo $base_time->format('Y-m-d H:i:s')."\n";

        //会員登録後ブリーダー未申請
        foreach ($customers as $customer) {
            $Breeder = $this->breedersRepository->find($customer->getId());

            if(!$Breeder){
                if($base_time > $customer->getCreateDate()){
                    echo "ブリーダー未申請".$customer->getEmail()."(".$customer->getId().")\n";
                    $this->mailService->sendBreederRemindRegist($customer);
                    sleep(1);
                }
            }
        }

        $Breeders = $this->breedersRepository->findBy(['examination_status' => AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK]);

        $base_time = new DateTime("-7days");
        echo $base_time->format('Y-m-d H:i:s')."\n";

        foreach ($Breeders as $Breeder) {
            if($base_time > $Breeder->getCreateDate()){
                $Customer = $this->customerRepository->find($Breeder->getId());
                $data = ['name' => $Breeder->getBreederName()];

                $hasDna = $this->dnaCheckStatusHeaderRepository->findBy(['site_type' => 1, 'register_id' => $Breeder->getId()]);
                if (!$hasDna) {
                    echo "DNA未請求".$Customer->getEmail()."(".$Breeder->getId().")\n";
                    $this->mailService->sendBreederRemindDna($Customer);
                    sleep(1);
                }
            }
            /*
            $Pet = $this->breederPetsRepository->findOneBy(['Breeder' => $Breeder], ['create_date' => 'DESC']);
            $isPetDay = $Pet && $Pet->getCreateDate()->format('Y-m-d') === (new DateTime('now -1month'))->format('Y-m-d');
            if ($isPetDay) {
                echo "Mail pet.\n";
                $this->mailService->sendBreederRemindPet($Customer->getEmail(), $data);
            }
            */
        }
        
        echo "Breeder remind mail handled.\n";
    }
}
