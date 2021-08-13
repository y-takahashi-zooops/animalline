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
        $query = $this->conservationPetsRepository->createQueryBuilder('c')
            ->join('c.breeds_type', 'bt')
            ->where('c.release_status = :release_status')
            ->setParameter('release_status', AnilineConf::RELEASE_STATUS_PUBLIC);

        if ($request->get('pet_kind')) {
            $query->andWhere('c.pet_kind = :pet_kind')
                ->setParameter('pet_kind', $request->get('pet_kind'));
        }

        if ($request->get('pet_sex')) {
            $query->andWhere('c.pet_sex = :pet_sex')
                ->setParameter('pet_sex', $request->get('pet_sex'));
        }

        if ($request->get('pref')) {
            $query->andWhere('bt.reeder_house_pref = :pref')
                ->setParameter('pref', $request->get('pref'));
        }

        return $query->addOrderBy('c.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}