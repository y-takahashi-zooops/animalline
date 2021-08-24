<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;

class BreederQueryService
{
    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository  $breederPetsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     */
    public function __construct (
        BreederPetsRepository     $breederPetsRepository,
        PetsFavoriteRepository     $petsFavoriteRepository
    ) {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
    }

    /**
     * Retrive breeder pets
     *
     * @param  Object $request
     * @return array
     */
    public function searchPetsResult($request): array
    {
        $query = $this->breederPetsRepository->createQueryBuilder('p')
            ->join('p.breeder_id', 'c')
            ->where('p.release_status = :release_status')
            ->setParameter('release_status', AnilineConf::RELEASE_STATUS_PUBLIC);

        if ($request->get('pet_kind')) {
            $query->andWhere('p.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $request->get('pet_kind'));
        }

        if ($request->get('breed_type')) {
            $query->andWhere('p.breeds_type = :breeds_type')
            ->setParameter('breeds_type', $request->get('breed_type'));
        }

        if ($request->get('gender')) {
            $query->andWhere('p.pet_sex = :pet_sex')
                ->setParameter('pet_sex', $request->get('gender'));
        }

        if ($request->get('region')) {
            $query->andWhere('c.breeder_house_pref = :pref')
                ->setParameter('pref', $request->get('region'));
        }

        return $query->addOrderBy('p.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBreederFavoritePets($customerId)
    {
        $query = $this->petsFavoriteRepository->createQueryBuilder('pf')
            ->select('bp')
            ->innerJoin('Customize\Entity\BreederPets', 'bp', 'WITH', 'bp.id = pf.id')
            ->orderBy('pf.update_date', 'DESC')
            ->where('pf.customer_id = :customer_id')
            ->setParameter('customer_id', $customerId)
            ->getQuery()
            ->getResult();
        return $query;
    }
}
