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
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportBenefitShippingSchedule extends Command
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
     * @var MailService
     */
    protected $mailService;

    /**
     * Breeder remind mail constructor.
     * 
     * @param EntityManagerInterface $entityManager
     * @param BreedersRepository $breedersRepository
     * @param BreederPetsRepository $breederPetsRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BreedersRepository $breedersRepository,
        BreederPetsRepository $breederPetsRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        CustomerRepository $customerRepository,
        MailService $mailService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->customerRepository = $customerRepository;
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

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $Breeders = $this->breedersRepository->findBy(['examination_status' => AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK]);

        foreach ($Breeders as $Breeder) {
            $Customer = $this->customerRepository->find($Breeder->getId());
            $data = ['name' => $Breeder->getBreederName()];

            $isBreederDay = $Breeder->getCreateDate()->format('Y-m-d') === (new DateTime('now -14days'))->format('Y-m-d');
            $hasDna = !!$this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $Breeder->getId()]);
            if ($isBreederDay && !$hasDna) {
                // mail dna
                $this->mailService->sendBreederRemindDna($Customer->getEmail(), $data);
            }

            $Pet = $this->breederPetsRepository->findOneBy(['Breeder' => $Breeder], ['create_date' => 'DESC']);
            $isPetDay = $Pet->getCreateDate()->format('Y-m-d') === (new DateTime('now -1month'))->format('Y-m-d');
            if ($isPetDay) {
                // mail pet
                $this->mailService->sendBreederRemindPet($Customer->getEmail(), $data);
            }
        }

        echo "breeder remind mail handled.\n";
    }
}
