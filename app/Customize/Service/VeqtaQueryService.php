<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckStatusRepository;

class VeqtaQueryService
{
    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

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
     */
    public function __construct(
        BreederPetsRepository          $breederPetsRepository,
        BreedsRepository               $breedsRepository,
        DnaCheckStatusRepository       $dnaCheckStatusRepository
    ) {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
     * Adoption member DNA filter.
     *
     * @param int $registerId
     * @param bool $isAll
     * @return array
     */
    public function filterPetList(): array
    {
        $queryConservation = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->leftJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->where('dna.check_status = 3')
            ->andWhere('dna.site_type = :site_type')
            ->setParameters(['site_type' => AnilineConf::ANILINE_SITE_TYPE_ADOPTION])
            ->select('dna.id as dna_id, dna.site_type, b.breeds_name, cp.pet_birthday, dna.check_status, dna.kit_pet_register_date');
        $resultConservation = $queryConservation->getQuery()->getArrayResult();

        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->where('dna.check_status = 3')
            ->andWhere('dna.site_type = :site_type')
            ->setParameters(['site_type' => AnilineConf::ANILINE_SITE_TYPE_BREEDER])
            ->select('dna.id as dna_id, dna.site_type, b.breeds_name, bp.pet_birthday, dna.check_status, dna.kit_pet_register_date');
        $resultBreeder = $queryBreeder->getQuery()->getArrayResult();

        $totalResult = array_merge($resultBreeder, $resultConservation);

        // order by update_date > dna_id desc
        usort(
            $totalResult,
            fn ($x, $y) =>
            [$y['update_date']->getTimestamp(), $y['dna_id']]
                <=>
                [$x['update_date']->getTimestamp(), $x['dna_id']]
        );

        return $totalResult;
    }
}
