<?php

namespace Customize\Service;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\PrefAdjacentRepository;

class AdoptionQueryService
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

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
     * AdoptionQueryService constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationsRepository $conservationsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param BreedsRepository $breedsRepository
     * @param PrefAdjacentRepository $prefAdjacentRepository
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository,
        PetsFavoriteRepository $petsFavoriteRepository,
        BreedsRepository $breedsRepository,
        PrefAdjacentRepository $prefAdjacentRepository,
        ConservationsRepository $conservationsRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breedsRepository = $breedsRepository;
        $this->prefAdjacentRepository = $prefAdjacentRepository;
        $this->conservationsRepository = $conservationsRepository;
    }

    /**
     * get breeds have pet
     *
     * @param mixed $petKind
     * @return array
     */
    public function getBreedsHavePet($petKind): array
    {
        return $this->breedsRepository->createQueryBuilder('b')
            ->select()
            ->leftJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'b.id = cp.BreedsType')
            ->where('b.pet_kind = :pet_kind and cp.BreedsType is not null')
            ->setParameter('pet_kind', $petKind)
            ->orderBy('b.breeds_name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieve  conservation pets
     *
     * @param mixed $petKind
     * @return array
     */
    public function getPetNew($petKind): array
    {
        $date_now = Carbon::now()->toDateString();
        $time_new = Carbon::now()->subMonth()->toDateString();

        $query = $this->conservationPetsRepository->createQueryBuilder('p')
            ->where('p.is_active = :release_status')
            ->setParameter('release_status', 1)
            ->andWhere('p.pet_kind = :pet_kind')
            ->setParameter('pet_kind', $petKind)
            ->andWhere('p.release_date <= :to')
            ->andWhere('p.release_date >= :from')
            ->setParameter(':to', $date_now)
            ->setParameter(':from', $time_new);

        return $query->addOrderBy('p.release_date', 'DESC')
            ->setMaxResults(AnilineConf::NUMBER_ITEM_TOP)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieve conservation pets
     *
     * @param $petKind
     * @return array
     */
    public function getPetFeatured($petKind): array
    {
        $query = $this->conservationPetsRepository->createQueryBuilder('p')
            ->where('p.is_active = :release_status')
            ->setParameter('release_status', 1)
            ->andWhere('p.pet_kind = :pet_kind')
            ->setParameter('pet_kind', $petKind)
            ->orderBy('p.favorite_count', 'DESC');
        return $query->addOrderBy('p.release_date', 'DESC')
            ->setMaxResults(AnilineConf::NUMBER_ITEM_TOP)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieve conservation pets
     *
     * @param Object $request
     * @return array
     */
    public function searchPetsResult($request): array
    {
        $query = $this->conservationPetsRepository->createQueryBuilder('p')
            ->join('p.Conservation', 'c')
            ->where('p.is_active = :release_status')
            ->setParameter('release_status', AnilineConf::IS_ACTIVE_PUBLIC);

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
            $query->innerJoin('Customize\Entity\ConservationsHouse', 'ch', 'WITH', 'c.id = ch.Conservation')
                ->andWhere('ch.Pref = :pref')
                ->setParameter('pref', $request->get('region'));
            if ($request->get('adjacent')) {
                $queryHouse = $this->prefAdjacentRepository->createQueryBuilder('pa')
                    ->andWhere('pa.pref_id = :pref')
                    ->setParameter('pref', $request->get('region'))
                    ->select('pa.adjacent_pref_id');

                $result = $queryHouse->getQuery()
                    ->getArrayResult();
                $arr = array_column($result, 'adjacent_pref_id');
                $query->orWhere('ch.Pref in (:arr)')
                    ->setParameter('arr', $arr)
                    ->andWhere('p.pet_kind = :pet_kind')
                    ->setParameter('pet_kind', $request->get('pet_kind'));
            }
        }

        if ($request->get('new_pet')) {
            $query->andWhere('p.release_date >= :fromDate')
                ->andWhere('p.release_date <= :toDate')
                ->setParameter('fromDate', Carbon::now()->subMonth()->toDateString())
                ->setParameter('toDate',Carbon::now()->toDateString());
        }

        if ($request->get('featured_pet')) {
            $query->orderBy('p.favorite_count', 'DESC');
        }

        return $query->addOrderBy('p.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * search adoptions result
     *
     * @param $request
     * @param $petKind
     * @return array
     */
    public function searchAdoptionsResult($request, $petKind): array
    {
        $query = $this->conservationsRepository->createQueryBuilder('c')
            ->leftJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'c.id = cp.Conservation')
            ->where('c.handling_pet_kind in (:kinds)')
            ->setParameter('kinds', [
                AnilineConf::ANILINE_PET_KIND_DOG_CAT,
                $petKind
            ]);

        if ($request->get('breed_type')) {
            $query->andWhere('cp.BreedsType = :breeds_type')
                ->setParameter('breeds_type', $request->get('breed_type'));
        }

        if ($request->get('region')) {
            // $query->innerJoin('Customize\Entity\ConservationHouse', 'ch', 'WITH', 'c.id = ch.Conservation')
            $query->innerJoin('Customize\Entity\ConservationsHouse', 'ch', 'WITH', 'c.id = ch.Conservation')
                ->andWhere('ch.Pref = :pref')
                ->setParameter('pref', $request->get('region'));
            if ($request->get('adjacent')) {
                $queryHouse = $this->prefAdjacentRepository->createQueryBuilder('pa')
                    ->andWhere('pa.pref_id = :pref')
                    ->setParameter('pref', $request->get('region'))
                    ->select('pa.adjacent_pref_id');

                $result = $queryHouse->getQuery()
                    ->getArrayResult();
                $arr = array_column($result, 'adjacent_pref_id');
                $query->orWhere('ch.Pref in (:arr)')
                    ->setParameter('arr', $arr);
            }
        }

        if ($request->get('license')) {
            // $query->andWhere('c.license_name LIKE :license OR c.license_house_name LIKE :license')
            $query->andWhere('c.organization_name LIKE :license')
                ->setParameter('license', '%' . $request->get('license') . '%');
        }

        return $query->addOrderBy('c.update_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * find adoption favorite pets
     *
     * @param $customerId
     * @return int|mixed|string
     */
    public function findAdoptionFavoritePets($customerId)
    {
        return $this->petsFavoriteRepository->createQueryBuilder('pf')
            ->select('cp')
            ->innerJoin('Customize\Entity\ConservationPets', 'cp', 'WITH', 'cp.id = pf.pet_id')
            ->orderBy('pf.update_date', 'DESC')
            ->where('pf.Customer = :customer_id')
            ->setParameter('customer_id', $customerId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Admin conservation pets
     *
     * @param array $criteria
     * @param array $order
     * @return array
     */
    public function filterPetAdmin(array $criteria, array $order): array
    {
        $qb = $this->conservationPetsRepository->createQueryBuilder('p');
        if (!empty($criteria['conservation_id'])) {
            $qb->andWhere('p.Conservation = :conservation_id')
                ->setParameter('conservation_id', $criteria['conservation_id']);
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
}
