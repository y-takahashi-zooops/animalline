<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PrefAdjacentRepository;

class AdoptionQueryService
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var PrefAdjacentRepository
     */
    protected $prefAdjacentRepository;

    /**
     * AdoptionQueryService constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param PrefAdjacentRepository $prefAdjacentRepository
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository,
        PrefAdjacentRepository     $prefAdjacentRepository
    )
    {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->prefAdjacentRepository = $prefAdjacentRepository;
    }

    /**
     * Retrive conservation pets
     *
     * @param Object $request
     * @return array
     */
    public function searchPetsResult($request)
    {
        $query = $this->conservationPetsRepository->createQueryBuilder('p')
            ->join('p.Conservation', 'c')
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
            $query->innerJoin('Customize\Entity\ConservationsHouse', 'bh', 'WITH', 'c.id = bh.Conservation')
                ->andWhere('bh.ConservationHousePref = :pref')
                ->setParameter('pref', $request->get('region'));
            if ($request->get('adjacent')) {
                $queryHouse = $this->prefAdjacentRepository->createQueryBuilder('pa')
                    ->andWhere('pa.pref_id = :pref')
                    ->setParameter('pref', $request->get('region'))
                    ->select('pa.adjacent_pref_id');

                $result = $queryHouse->getQuery()
                    ->getArrayResult();
                $arr = array_column($result, 'adjacent_pref_id');
                $query->orWhere('bh.ConservationHousePref in (:arr)')
                    ->setParameter('arr', $arr);
            }
            return $query->addOrderBy('p.release_date', 'DESC')
                ->getQuery()
                ->getResult();
        }
    }
}