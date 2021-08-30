<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\PrefAdjacentRepository;
use setasign\Fpdi\PdfParser\CrossReference\AbstractReader;

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
     * @var PrefAdjacentRepository
     */
    protected $prefAdjacentRepository;

    /**
     * BreederQueryService constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param PrefAdjacentRepository $prefAdjacentRepository
     */
    public function __construct(
        BreederPetsRepository  $breederPetsRepository,
        PetsFavoriteRepository $petsFavoriteRepository,
        PrefAdjacentRepository $prefAdjacentRepository
    )
    {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->prefAdjacentRepository = $prefAdjacentRepository;
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
            $query->andWhere('p.BreedType = :breeds_type')
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
                    ->setParameter('pet_kind', $request->get('pet_kind'));;
            }

        }

        return $query->addOrderBy('p.release_date', 'DESC')
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
}
