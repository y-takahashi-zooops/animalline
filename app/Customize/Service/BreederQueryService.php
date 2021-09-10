<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\PrefAdjacentRepository;

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
     * @var PrefAdjacentRepository
     */
    protected $prefAdjacentRepository;

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param PrefAdjacentRepository $prefAdjacentRepository
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreederPetsRepository  $breederPetsRepository,
        PetsFavoriteRepository $petsFavoriteRepository,
        PrefAdjacentRepository $prefAdjacentRepository,
        BreedersRepository     $breedersRepository
    )
    {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->prefAdjacentRepository = $prefAdjacentRepository;
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * Retrive breeder pets
     *
     * @param Object $request
     * @return array
     */
    public function searchPetsResult($request): array
    {
        $query = $this->breederPetsRepository->createQueryBuilder('p')
            ->join('p.Breeder', 'c')
            ->where('p.release_status = :release_status')
            ->setParameter('release_status', AnilineConf::RELEASE_STATUS_PUBLIC);

        if ($request->get('pet_kind')) {
            $query->andWhere('p.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $request->get('pet_kind'));
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

        return $query->addOrderBy('p.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchBreedersResult($request, $petKind): array
    {
        $query = $this->breedersRepository->createQueryBuilder('b')
            ->innerJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'b.id = bp.Breeder')
            ->where('bp.pet_kind = :pet_kind')
            ->setParameter('pet_kind', $petKind);

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
                    ->setParameter('arr', $arr)
                    ->andWhere('bp.pet_kind = :pet_kind')
                    ->setParameter('pet_kind', $request->get('pet_kind'));
            }
        }

        if ($request->get('license')) {
            $query->andWhere('b.license_name LIKE :license OR b.license_house_name LIKE :license')
                ->setParameter('license', '%' . $request->get('license') .'%');
        }

        return $query->addOrderBy('b.update_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBreederFavoritePets($customerId)
    {
        $query = $this->petsFavoriteRepository->createQueryBuilder('pf')
            ->select('bp')
            ->innerJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'bp.id = pf.pet_id')
            ->orderBy('pf.update_date', 'DESC')
            ->where('pf.Customer = :customer_id')
            ->setParameter('customer_id', $customerId)
            ->getQuery()
            ->getResult();
        return $query;
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
}