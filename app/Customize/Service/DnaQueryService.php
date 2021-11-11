<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
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
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    const EXCLUDES = [
        AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED,
        AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG,
        AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT,
        AnilineConf::ANILINE_DNA_CHECK_STATUS_PUBLIC
    ];

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreedsRepository $breedsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param BreedersRepository $breedersRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     */
    public function __construct(
        BreederPetsRepository          $breederPetsRepository,
        BreedsRepository               $breedsRepository,
        DnaCheckStatusRepository       $dnaCheckStatusRepository,
        BreedersRepository             $breedersRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
    ) {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->breedersRepository = $breedersRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
    }

    /**
     * Admin DNA filter
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
            ->innerJoin('dna.DnaHeader', 'dnah')
            ->leftJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->join('Eccube\Entity\Customer', 'c', 'WITH', 'dnah.register_id = c.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->where('dna.site_type = :site_type')
            ->setParameters(['site_type' => AnilineConf::ANILINE_SITE_TYPE_ADOPTION])
            ->select('dna.id as dna_id, dna.site_type, cp.id as pet_id, c.id as customer_id, cp.thumbnail_path, cp.pet_kind, b.breeds_name, dna.check_status, dnah.kit_shipping_date, dna.kit_return_date, dna.check_return_date, dna.file_path, dna.update_date,c.name01,c.name02');
        if (!empty($customerName)) {
            $queryConservation->andWhere('c.name01 like :customer_name or c.name02 like :customer_name')
                ->setParameter(':customer_name', '%' . $criteria['customer_name'] . '%');
        }
        if (!empty($petKind)) {
            $queryConservation->andWhere('cp.pet_kind = :pet_kind')
                ->setParameter(':pet_kind', $petKind);
        }
        if (!empty($checkStatus)) {
            $queryConservation->andWhere('dna.check_status = :check_status')
                ->setParameter(':check_status', $checkStatus);
        }
        $resultConservation = $queryConservation->getQuery()->getArrayResult();

        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->innerJoin('dna.DnaHeader', 'dnah')
            ->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->join('Eccube\Entity\Customer', 'c', 'WITH', 'dnah.register_id = c.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->where('dna.site_type = :site_type')
            ->setParameters(['site_type' => AnilineConf::ANILINE_SITE_TYPE_BREEDER])
            ->select('dna.id as dna_id, dna.site_type, bp.id as pet_id, c.id as customer_id, bp.thumbnail_path, bp.pet_kind, b.breeds_name, dna.check_status, dnah.kit_shipping_date, dna.kit_return_date, dna.check_return_date, dna.file_path, dna.update_date,c.name01,c.name02');
        if (!empty($customerName)) {
            $queryBreeder->andWhere('c.name01 like :customer_name or c.name02 like :customer_name')
                ->setParameter(':customer_name', '%' . $criteria['customer_name'] . '%');
        }
        if (!empty($petKind)) {
            $queryBreeder->andWhere('bp.pet_kind = :pet_kind')
                ->setParameter(':pet_kind', $petKind);
        }
        if (!empty($checkStatus)) {
            $queryBreeder->andWhere('dna.check_status = :check_status')
                ->setParameter(':check_status', $checkStatus);
        }
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
     * Breeder member DNA filter.
     *
     * @param int $registerId
     * @param bool $isAll
     * @return array
     */
    public function filterDnaBreederMember(int $registerId, bool $isAll): array
    {
        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->innerJoin('dna.DnaHeader', 'dnah')
            ->join('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->where('dnah.register_id = :register_id')
            ->andWhere('dna.site_type = :site_type')
            ->setParameters([':register_id' => $registerId, ':site_type' => AnilineConf::ANILINE_SITE_TYPE_BREEDER])
            ->select('dna.id as dna_id, bp.id as pet_id, bp.thumbnail_path, bp.pet_kind, b.breeds_name, dna.check_status, dnah.kit_shipping_date, dna.kit_return_date, dna.check_return_date');
        if (!$isAll) {
            $queryBreeder->andWhere($queryBreeder->expr()->notIn('dna.check_status', self::EXCLUDES));
        }
        return $queryBreeder->orderBy('dna.update_date', 'DESC')
            ->addOrderBy('dna.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Adoption member DNA filter.
     *
     * @param int $registerId
     * @param bool $isAll
     * @return array
     */
    public function filterDnaAdoptionMember(int $registerId, bool $isAll): array
    {
        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->join('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->join('Customize\Entity\DnaCheckStatusHeader', 'dcsh', 'WITH', 'dna.DnaHeader = dcsh.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->where('dcsh.register_id = :register_id')
            ->andWhere('dna.site_type = :site_type')
            ->setParameters([':register_id' => $registerId, ':site_type' => AnilineConf::ANILINE_SITE_TYPE_ADOPTION])
            ->select('dna.id as dna_id, cp.id as pet_id, cp.thumbnail_path, cp.pet_kind, b.breeds_name, dna.check_status, dcsh.kit_shipping_date, dna.kit_return_date, dna.check_return_date');
        if (!$isAll) {
            $queryBreeder->andWhere($queryBreeder->expr()->notIn('dna.check_status', self::EXCLUDES));
        }
        return $queryBreeder->orderBy('dna.update_date', 'DESC')
            ->addOrderBy('dna.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
