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
        $kitRegistDateFrom = $criteria['kit_regist_date_from'] ?? '';
        $kitRegistDateTo = $criteria['kit_regist_date_to'] ?? '';
        $kitReturnDateFrom = $criteria['kit_return_date_from'] ?? '';
        $kitReturnDateTo = $criteria['kit_return_date_to'] ?? '';
        $checkReturnDateFrom = $criteria['check_return_date_from'] ?? '';
        $checkReturnDateTo = $criteria['check_return_date_to'] ?? '';
        $fromTime = ' 00:00:00';
        $toTime = ' 23:59:59';

        $queryConservation = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->innerJoin('dna.DnaHeader', 'dnah')
            ->leftJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'dna.pet_id = cp.id')
            ->join('Eccube\Entity\Customer', 'c', 'WITH', 'dnah.register_id = c.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->where('dna.site_type = :site_type')
            ->setParameters(['site_type' => AnilineConf::ANILINE_SITE_TYPE_ADOPTION])
            ->select('dna.id as dna_id, dna.site_type, cp.id as pet_id, c.id as customer_id, cp.thumbnail_path, cp.pet_kind, b.breeds_name, dna.check_status, dnah.kit_shipping_date, dna.kit_return_date, dna.check_return_date, dna.file_path, dna.update_date,c.name01,c.name02,dnah.shipping_name,dnah.shipping_pref,dnah.shipping_city,dnah.shipping_address');
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
        if (!empty($kitRegistDateFrom)) {
            $fromDatetime = $kitRegistDateFrom . $fromTime;
            $queryConservation
                ->andWhere("dnah.kit_shipping_date >= '$fromDatetime'");
        }
        if (!empty($kitRegistDateTo)) {
            $toDatetime = $kitRegistDateTo . $toTime;
            $queryConservation
                ->andWhere("dnah.kit_shipping_date <= '$toDatetime'");
        }
        if (!empty($kitReturnDateFrom)) {
            $fromDatetime = $kitReturnDateFrom . $fromTime;
            $queryConservation
                ->andWhere("dna.kit_return_date >= '$fromDatetime'");
        }
        if (!empty($kitReturnDateTo)) {
            $toDatetime = $kitReturnDateTo . $toTime;
            $queryConservation
                ->andWhere("dna.kit_return_date <= '$toDatetime'");
        }
        if (!empty($checkReturnDateFrom)) {
            $fromDatetime = $checkReturnDateFrom . $fromTime;
            $queryConservation
                ->andWhere("dna.check_return_date >= '$fromDatetime'");
        }
        if (!empty($checkReturnDateTo)) {
            $toDatetime = $checkReturnDateTo . $toTime;
            $queryConservation
                ->andWhere("dna.check_return_date <= '$toDatetime'");
        }

        $resultConservation = $queryConservation->getQuery()->getArrayResult();

        $queryBreeder = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->innerJoin('dna.DnaHeader', 'dnah')
            ->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'dna.pet_id = bp.id')
            ->join('Eccube\Entity\Customer', 'c', 'WITH', 'dnah.register_id = c.id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->where('dna.site_type = :site_type')
            ->setParameters(['site_type' => AnilineConf::ANILINE_SITE_TYPE_BREEDER])
            ->select('dna.id as dna_id, dna.site_type, bp.id as pet_id, c.id as customer_id, bp.thumbnail_path, bp.pet_kind, b.breeds_name, dna.check_status, dnah.kit_shipping_date, dna.kit_return_date, dna.check_return_date, dna.file_path, dna.update_date,c.name01,c.name02,dnah.shipping_name,dnah.shipping_pref,dnah.shipping_city,dnah.shipping_address');
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
        if (!empty($kitRegistDateFrom)) {
            $fromDatetime = $kitRegistDateFrom . $fromTime;
            $queryBreeder
                ->andWhere("dnah.kit_shipping_date >= '$fromDatetime'");
        }
        if (!empty($kitRegistDateTo)) {
            $toDatetime = $kitRegistDateTo . $toTime;
            $queryBreeder
                ->andWhere("dnah.kit_shipping_date <= '$toDatetime'");
        }
        if (!empty($kitReturnDateFrom)) {
            $fromDatetime = $kitReturnDateFrom . $fromTime;
            $queryBreeder
                ->andWhere("dna.kit_return_date >= '$fromDatetime'");
        }
        if (!empty($kitReturnDateTo)) {
            $toDatetime = $kitReturnDateTo . $toTime;
            $queryBreeder
                ->andWhere("dna.kit_return_date <= '$toDatetime'");
        }
        if (!empty($checkReturnDateFrom)) {
            $fromDatetime = $checkReturnDateFrom . $fromTime;
            $queryBreeder
                ->andWhere("dna.check_return_date >= '$fromDatetime'");
        }
        if (!empty($checkReturnDateTo)) {
            $toDatetime = $checkReturnDateTo . $toTime;
            $queryBreeder
                ->andWhere("dna.check_return_date <= '$toDatetime'");
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

    /**
     * @param int $month
     * @param int $year
     * @param int $day
     *
     * @return object[]
     */
    public function findByDate($year, $month, $day)
    {
        $startDate = new \DateTimeImmutable("$year-$month-$day 00:00:00");
        $endDate = new \DateTimeImmutable("$year-$month-$day 23:59:59");
        $qb = $this->dnaCheckStatusRepository->createQueryBuilder('dna')
            ->join('Customize\Entity\DnaCheckStatusHeader', 'dnah', 'WITH', 'dna.DnaHeader = dnah.id')
            ->join('Customize\Entity\Breeders', 'b', 'WITH', 'dnah.register_id = b.id')
            ->where('dna.site_type = :site')
            ->andWhere('dna.check_return_date BETWEEN :start AND :end')
            ->andWhere('dna.check_status = :check_status')
            ->setParameter('site', AnilineConf::SITE_CATEGORY_BREEDER)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('check_status', 9)
            ->select('dna, b.id as breeder_id, dna.pet_id');

        return $qb->getQuery()->getResult();
    }
}
