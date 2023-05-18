<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Service\BreederQueryService;
use Customize\Service\AdoptionQueryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Carbon\Carbon;

class NewPetLotation extends Command
{
    protected static $defaultName = 'eccube:customize:pet-lotetion';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;


    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * MovieConvert constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param BreederQueryService $breederQueryService
     * @param AdoptionQueryService $adoptionQueryService
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param BreederPetsRepository $breederPetsRepository
     */
    public function __construct(
        EntityManagerInterface      $entityManager,
        BreederQueryService         $breederQueryService,
        AdoptionQueryService        $adoptionQueryService,
        ConservationPetsRepository   $conservationPetsRepository,
        BreederPetsRepository       $breederPetsRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->breederQueryService = $breederQueryService;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->conservationPetRepository = $conservationPetsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
    }

    protected function configure()
    {
        $this->setDescription('Convert movie format');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date_now = Carbon::now();

        for($i=1;$i<=2;$i++){
            //Breeder
            $query = $this->breederPetsRepository->createQueryBuilder('p')
                ->where('p.is_active = 1')
                ->andWhere('p.is_delete = 0')
                ->andWhere('p.is_contract = 0')
                ->andWhere('p.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $i)
                ->addOrderBy('p.update_date', 'asc');
            
            $pets = $query->getQuery()->getResult();

            foreach($pets as $pet) {
                echo $pet->getUpdateDate()->format("Y-m-d H:i:s")."\n";
                
                $pet->setUpdateDate($date_now);
                $this->entityManager->persist($pet);
                $this->entityManager->flush();

                break;
            }
        }
        
        return 0;
    }
}
