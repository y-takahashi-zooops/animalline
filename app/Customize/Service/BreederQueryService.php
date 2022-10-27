<?php

namespace Customize\Service;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\PrefAdjacentRepository;
use Doctrine\ORM\NonUniqueResultException;

class BreederQueryService
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
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var PrefAdjacentRepository
     */
    protected $prefAdjacentRepository;

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param BreedsRepository $breedsRepository
     * @param PrefAdjacentRepository $prefAdjacentRepository
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreederPetsRepository  $breederPetsRepository,
        PetsFavoriteRepository $petsFavoriteRepository,
        BreedsRepository $breedsRepository,
        PrefAdjacentRepository $prefAdjacentRepository,
        BreedersRepository     $breedersRepository
    ) {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breedsRepository = $breedsRepository;
        $this->prefAdjacentRepository = $prefAdjacentRepository;
        $this->breedersRepository = $breedersRepository;
    }

    public function getBreedsHavePet($petKind): array
    {
        return $this->breedsRepository->createQueryBuilder('b')
            ->select()
            ->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'b.id = bp.BreedsType')
            ->where('b.pet_kind = :pet_kind and bp.BreedsType is not null')
            ->setParameter('pet_kind', $petKind)
            ->orderBy('b.breeds_name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieve breeder pets
     *
     * @param mixed $petKind
     * @return array
     */
    public function getPetNew($petKind): array
    {
        $date_now = Carbon::now()->toDateString();
        $time_new = Carbon::now()->subMonth()->toDateString();

        $query = $this->breederPetsRepository->createQueryBuilder('p')
            ->where('p.is_active = :is_active')
            //->andWhere('p.is_delete = 0')
            ->setParameter('is_active', 1)
            ->andWhere('p.pet_kind = :pet_kind')
            ->setParameter('pet_kind', $petKind)
            ->orderBy('p.is_delete', 'asc')
            ->addOrderBy('p.dna_check_result', 'desc')
            ->addOrderBy('p.thumbnail_path', 'desc');
            
            //->setMaxResults(16);
        /*
            ->andWhere('p.release_date <= :to')
            ->andWhere('p.release_date >= :from')
            ->setParameter(':to', $date_now)
            ->setParameter(':from', $time_new);
            */

        return $query->getQuery()->getResult();
    }

    /**
     * Retrieve breeder pets
     *
     * @param mixed $petKind
     * @return array
     */
    public function getPetFeatured($petKind): array
    {
        $query = $this->breederPetsRepository->createQueryBuilder('p')
            ->where('p.is_active = :is_active')
            ->andWhere('p.is_delete = 0')
            ->setParameter('is_active', 1)
            ->andWhere('p.pet_kind = :pet_kind')
            ->setParameter('pet_kind', $petKind)
            ->orderBy('p.favorite_count', 'DESC');
        return $query->addOrderBy('p.release_date', 'DESC')
            ->setMaxResults(AnilineConf::NUMBER_ITEM_TOP)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrive breeder pets
     *
     * @param Object $request
     * @return array
     */
    public function searchPetsResult($request): array
    {
        $date_now = Carbon::now()->toDateString();
        $time_new = Carbon::now()->subMonth()->toDateString();

        $query = $this->breederPetsRepository->createQueryBuilder('p')
            ->join('p.Breeder', 'c')
            ->where('p.is_active = :is_active')
            //->andWhere('p.is_delete = 0')
            ->setParameter('is_active', 1);

        if ($request->get('pet_kind')) {
            $query->andWhere('p.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $request->get('pet_kind'));
        }

        if ($request->get('size_code') && $request->get('pet_kind') == AnilineConf::ANILINE_PET_KIND_DOG) {
            $query->join('p.BreedsType', 'b')
                ->andWhere('b.size_code = :size_code')
                ->setParameter('size_code', $request->get('size_code'));
        }

        if ($request->get('breed_type')) {
            $query->andWhere('p.BreedsType = :breeds_type')
                ->setParameter('breeds_type', $request->get('breed_type'));
        }

        if ($request->get('gender')) {
            $query->andWhere('p.pet_sex = :pet_sex')
                ->setParameter('pet_sex', $request->get('gender'));
        }

        if ($request->get('region')) {
            $query->innerJoin('Customize\Entity\BreederHouse', 'bh', 'WITH', 'c.id = bh.Breeder')
                ->andWhere('bh.BreederHousePrefId = :pref')
                ->setParameter('pref', $request->get('region'));
            if ($request->get('adjacent')) {
                $queryHouse = $this->prefAdjacentRepository->createQueryBuilder('pa')
                    ->andWhere('pa.pref_id = :pref')
                    ->setParameter('pref', $request->get('region'))
                    ->select('pa.adjacent_pref_id');

                $result = $queryHouse->getQuery()
                    ->getArrayResult();
                $arr = array_column($result, 'adjacent_pref_id');
                $query->orWhere('bh.BreederHousePrefId in (:arr)')
                    ->setParameter('arr', $arr)
                    ->andWhere('p.pet_kind = :pet_kind')
                    ->setParameter('pet_kind', $request->get('pet_kind'));
            }
        }

        if ($request->get('pet_new')) {
            $query->andWhere('p.release_date <= :to')
                ->andWhere('p.release_date >= :from')
                ->setParameter(':to', $date_now)
                ->setParameter(':from', $time_new);
        }

        $query->orderBy('p.is_delete', 'asc')
            ->addOrderBy('p.dna_check_result', 'desc')
            ->addOrderBy('p.thumbnail_path', 'desc');

        /*
        if ($request->get('featured_pet')) {
            $query->orderBy('p.favorite_count', 'DESC');
        }
        */
        return $query->addOrderBy('p.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieve breeder pets
     *
     * @param Object $request
     * @param int $petKind
     * @return array
     */
    public function searchBreedersResult($request, int $petKind): array
    {
        $query = $this->breedersRepository->createQueryBuilder('b');
        $query->leftJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'b.id = bp.Breeder')
            ->where('b.handling_pet_kind in (:kinds) and b.is_active = 1')
            ->andWhere($query->expr()->In('b.examination_status', AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK))
            ->setParameter('kinds', [
                AnilineConf::ANILINE_PET_KIND_DOG_CAT,
                $petKind
            ]);

        if ($request->get('breed_type')) {
            $query->andWhere('bp.BreedsType = :breeds_type')
                ->setParameter('breeds_type', $request->get('breed_type'));
        }

        if ($request->get('region')) {
            $query->innerJoin('Customize\Entity\BreederHouse', 'bh', 'WITH', 'b.id = bh.Breeder')
                ->andWhere('bh.BreederHousePrefId = :pref')
                ->setParameter('pref', $request->get('region'));
            if ($request->get('adjacent')) {
                $queryHouse = $this->prefAdjacentRepository->createQueryBuilder('pa')
                    ->andWhere('pa.pref_id = :pref')
                    ->setParameter('pref', $request->get('region'))
                    ->select('pa.adjacent_pref_id');

                $result = $queryHouse->getQuery()
                    ->getArrayResult();
                $arr = array_column($result, 'adjacent_pref_id');
                $query->orWhere('bh.BreederHousePrefId in (:arr)')
                    ->setParameter('arr', $arr);
            }
        }

        if ($request->get('license')) {
            $query->andWhere('b.license_name LIKE :license OR b.license_house_name LIKE :license')
                ->setParameter('license', '%' . $request->get('license') . '%');
        }

        return $query->addOrderBy('b.update_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrive breeder pets
     *
     * @param int $customerId
     * @return array
     */
    public function findBreederFavoritePets(int $customerId): array
    {
        return $this->petsFavoriteRepository->createQueryBuilder('pf')
            ->select('bp')
            ->innerJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'bp.id = pf.pet_id')
            ->orderBy('pf.update_date', 'DESC')
            ->where('pf.Customer = :customer_id')
            ->setParameter('customer_id', $customerId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieve breeder pets
     *
     * @param Object $breederId
     * @return float
     * @throws NonUniqueResultException
     */
    public function calculateBreederRank($breederId): float
    {
        $result = $this->breedersRepository->createQueryBuilder('b')
            ->join('Customize\Entity\BreederPets', 'bp', 'WITH', 'b.id = bp.Breeder')
            ->join('Customize\Entity\BreederEvaluations', 'be', 'WITH', 'be.Pet = bp.id')
            ->where('b.id = :breeder_id')
            ->andWhere('be.is_active = 2')
            ->setParameter('breeder_id', $breederId)
            ->select('avg(be.evaluation_value) as avg_evaluation')
            ->getQuery()
            ->getOneOrNullResult();

        return round($result['avg_evaluation'], 1);
    }

    /**
     * Admin breeder pets
     *
     * @param array $criteria
     * @param array $order
     * @return array
     */
    public function filterPetAdmin(array $criteria, array $order): array
    {
        $qb = $this->breederPetsRepository->createQueryBuilder('p');
        if (!empty($criteria['id'])) {
            $qb->andWhere('p.Breeder = :id')
                ->setParameter('id', $criteria['id']);
        }

        if (!empty($criteria['pet_kind'])) {
            $qb->andWhere('p.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $criteria['pet_kind']);
        }

        if (!empty($criteria['breed_type'])) {
            $qb->andWhere('p.BreedsType = :breed_type')
                ->setParameter('breed_type', $criteria['breed_type']);
        }

        if ($order['field'] == 'breed_type') {
            return $qb->join('p.BreedsType', 'b')
                ->orderBy('b.breeds_name', $order['direction'])
                ->addOrderBy('p.create_date', $order['direction'])
                ->getQuery()
                ->getResult();
        }

        return $qb->orderBy('p.' . $order['field'], $order['direction'])
            ->getQuery()
            ->getResult();
    }

    /**
     * list breeder pets
     */
    public function getListPet($breeder)
    {
        /*
        $status = $this->breederPetsRepository->createQueryBuilder('bp2')
            ->join('Customize\Entity\DnaCheckStatus', 'dna2', 'WITH', 'bp2.id = dna2.pet_id')
            ->where('dna2.check_status = 8')
            ->select('bp2.id')
            ->getDQL();
        */

        $qb = $this->breederPetsRepository->createQueryBuilder('bp');
        return $qb
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'b.id = bp.BreedsType')
            ->leftJoin('Customize\Entity\BreederContactHeader', 'bch', 'WITH', 'bch.Pet = bp.id')
            ->where('bp.Breeder = :breeder')
            ->setParameter('breeder', $breeder)
            //->andWhere($qb->expr()->notIn('bp.id', $status))
            //->andWhere('bp.is_delete = 0')
            ->orderBy('bp.is_delete', 'DESC')
            ->addOrderBy('bch.last_message_date', 'ASC')
            ->select('bp, bch.id as bch_id, bch.last_message_date as last_msg,bch.contract_status, b.breeds_name, bp.is_delete, bp.movie_file')
            ->getQuery()
            ->getScalarResult();
    }
}
