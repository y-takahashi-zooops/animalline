<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckStatusRepository;

class DnaQueryService
{
    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreedsRepository $breedsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreederPetsRepository  $breederPetsRepository,
        BreedsRepository $breedsRepository,
        DnaCheckStatusRepository $dnaCheckStatusRepository,
        BreedersRepository     $breedersRepository
    ) {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * Admin dna filter
     *
     * @param array $criteria
     * @return array
     */
    public function filterDnaAdmin(array $criteria): array
    {
        $qb = $this->dnaCheckStatusRepository->createQueryBuilder('d');
/*        if (!empty($criteria['id'])) {
            $qb->andWhere('p.Breeder = :id')
                ->setParameter('id', $criteria['id']);
        }

        if (!empty($criteria['pet_kind'])) {
            if
            $qb->andWhere('p.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $criteria['pet_kind']);
        }*/

        if (!empty($criteria['check_status'])) {
            $qb->andWhere('d.check_status = :check_status')
                ->setParameter('check_status', $criteria['check_status']);
        }

        return $qb->orderBy('d.update_date', 'DESC')
            ->addOrderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
