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

    private $excludes = [
        AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED,
        AnilineConf::ANILINE_DNA_CHECK_STATUS_NG,
        AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT
    ];

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreedsRepository $breedsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreederPetsRepository    $breederPetsRepository,
        BreedsRepository         $breedsRepository,
        DnaCheckStatusRepository $dnaCheckStatusRepository,
        BreedersRepository       $breedersRepository
    )
    {
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
        $customerName = $criteria['customer_name'] ?? '';
        $petKind = $criteria['pet_kind'] ?? '';
        $checkStatus = $criteria['check_status'] ?? '';

        $queryConservation = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->join('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->join('Eccube\Entity\Customer', 'c', 'WITH', 'dna.register_id = c.id and dna.register_id = cp.Conservation')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->select('dna.id as dna_id, cp.id as pet_id, c.id as customer_id, cp.thumbnail_path, cp.pet_kind, b.breeds_name, dna.check_status, dna.kit_shipping_date, dna.kit_return_date, dna.check_return_date');
        if (!empty($customerName))
            $queryConservation->andWhere('c.name01 like :customer_name or c.name02 like :customer_name')
                ->setParameter(':customer_name', '%' . $criteria['customer_name'] . '%');
        if (!empty($petKind))
            $queryConservation->andWhere('cp.pet_kind = :pet_kind')
                ->setParameter(':pet_kind', $petKind);
        if (!empty($checkStatus))
            $queryConservation->andWhere('dna.check_status = :check_status')
                ->setParameter(':check_status', $checkStatus);
        $resultConservation = $queryConservation->orderBy('dna.update_date', 'DESC')
            ->addOrderBy('dna.id', 'DESC')->getQuery()->getArrayResult();

        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->join('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->join('Eccube\Entity\Customer', 'c', 'WITH', 'dna.register_id = c.id and dna.register_id = bp.Breeder')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->select('dna.id as dna_id, bp.id as pet_id, c.id as customer_id, bp.thumbnail_path, bp.pet_kind, b.breeds_name, dna.check_status, dna.kit_shipping_date, dna.kit_return_date, dna.check_return_date');
        if (!empty($customerName))
            $queryBreeder->andWhere('c.name01 like :customer_name or c.name02 like :customer_name')
                ->setParameter(':customer_name', '%' . $criteria['customer_name'] . '%');
        if (!empty($petKind))
            $queryBreeder->andWhere('bp.pet_kind = :pet_kind')
                ->setParameter(':pet_kind', $petKind);
        if (!empty($checkStatus))
            $queryBreeder->andWhere('dna.check_status = :check_status')
                ->setParameter(':check_status', $checkStatus);
        $resultBreeder = $queryBreeder->orderBy('dna.update_date', 'DESC')
            ->addOrderBy('dna.id', 'DESC')->getQuery()->getArrayResult();

        return array_merge($resultBreeder, $resultConservation);
    }

    /**
     * Adoption dna filter
     *
     * @param array $criteria
     * @return array
     */
    public function filterDnaAdoption(array $criteria): array
    {
        $queryConservation = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->join('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->select('dna.id as dna_id, cp.id as pet_id, cp.thumbnail_path, cp.pet_kind, b.breeds_name, 
            dna.check_status, dna.kit_shipping_date, dna.kit_return_date, dna.check_return_date')
            ->where('dna.register_id =:adoption_id')
            ->setParameter(':adoption_id', $criteria['conservation_id'])
            ->andWhere('dna.site_type =:site_type')
            ->setParameter(':site_type', AnilineConf::ANILINE_SITE_TYPE_ADOPTION);
        if (!array_key_exists('is_all', $criteria)) {
            $queryConservation->andWhere('dna.check_status NOT IN (:excludes)')->setParameter(':excludes', $this->excludes);
        }

        return $queryConservation->orderBy('dna.update_date', 'DESC')
            ->addOrderBy('dna.id', 'DESC')->getQuery()->getArrayResult();
    }
}
