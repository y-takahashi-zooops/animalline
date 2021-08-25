<?php

namespace Customize\Service;

use Customize\Config\AnilineConf;
use Customize\Repository\ConservationPetsRepository;

class AdoptionQueryService
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * AdoptionQueryService constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     */
    public function __construct (
        ConservationPetsRepository     $conservationPetsRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
    }

    /**
     * Retrive conservation pets
     * 
     * @param  Object $request
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
            $query->andWhere('c.PrefId = :pref')
                ->setParameter('pref', $request->get('region'));
        }

        return $query->addOrderBy('p.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}