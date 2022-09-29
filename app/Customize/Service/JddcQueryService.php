<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckStatusRepository;

class JddcQueryService
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
        BreederPetsRepository    $breederPetsRepository,
        BreedsRepository         $breedsRepository,
        DnaCheckStatusRepository $dnaCheckStatusRepository
    ) {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
     * Filter Pet List.
     *
     * @param $filter_status
     * @return array
     */
    public function filterPetList($filter_status): array
    {
        $status = [AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED, AnilineConf::ANILINE_DNA_CHECK_STATUS_CHECKING];
        $queryConservation = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->leftJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->leftJoin('Customize\Entity\DnaCheckStatusHeader', 'dnah', 'WITH', 'dna.DnaHeader = dnah.id')
            ->where('dna.check_status IN (:status)')
            ->andWhere('dna.site_type = :site_type')
            ->andWhere('dnah.labo_type = 2')
            ->setParameter('site_type', AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
            ->setParameter('status', $filter_status ?: $status)
            ->select('dna.id as dna_id, dna.site_type, b.pet_kind, b.id as breeds_id, b.breeds_name, cp.pet_birthday, dna.check_status, dna.kit_pet_register_date, dna.update_date, dnah.shipping_name');
        $resultConservation = $queryConservation->getQuery()->getArrayResult();

        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->leftJoin('Customize\Entity\DnaCheckStatusHeader', 'dnah', 'WITH', 'dna.DnaHeader = dnah.id')
            ->where('dna.check_status IN (:status)')
            ->andWhere('dna.site_type = :site_type')
            ->andWhere('dnah.labo_type = 2')
            ->setParameter('site_type', AnilineConf::ANILINE_SITE_TYPE_BREEDER)
            ->setParameter('status', $filter_status ?: $status)
            ->select('dna.id as dna_id, dna.site_type, b.pet_kind, b.id as breeds_id, b.breeds_name, bp.pet_birthday, dna.check_status, dna.kit_pet_register_date, dna.update_date, dnah.shipping_name');
        $resultBreeder = $queryBreeder->getQuery()->getArrayResult();

        $totalResult = array_merge($resultBreeder, $resultConservation);

        // order by update_date > dna_id desc
        usort(
            $totalResult,
            function ($x, $y) {
                return [$y['update_date']->getTimestamp(), $y['dna_id']]
                    <=>
                    [$x['update_date']->getTimestamp(), $x['dna_id']];
            }
        );

        return $totalResult;
    }

    /**
     * Filter Pet List.
     *
     * @param $filter_status
     * @return array
     */
    public function completePetList(): array
    {
        $status = [AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED, AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG];
        
        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->leftJoin('Customize\Entity\DnaCheckStatusHeader', 'dnah', 'WITH', 'dna.DnaHeader = dnah.id')
            ->where('dna.check_status IN (:status)')
            ->andWhere('dna.site_type = :site_type')
            ->andWhere('dnah.labo_type = 2')
            ->setParameter('site_type', AnilineConf::ANILINE_SITE_TYPE_BREEDER)
            ->setParameter('status', $status)
            ->select('dna.id as dna_id, dna.site_type, b.pet_kind, b.id as breeds_id, b.breeds_name, bp.pet_birthday, dna.check_status, dna.kit_pet_register_date, dna.check_return_date, dnah.shipping_name')
            ->orderBy('dna.check_return_date', 'desc');
        $resultBreeder = $queryBreeder->getQuery()->getArrayResult();

        return $resultBreeder;
    }

}
