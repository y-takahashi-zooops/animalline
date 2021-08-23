<?php

namespace Customize\Service;

use Customize\Repository\PetsFavoriteRepository;

class BreederQueryService
{
    protected $petsFavoriteRepository;

    public function __construct(
        PetsFavoriteRepository     $petsFavoriteRepository
    ) {
        $this->petsFavoriteRepository = $petsFavoriteRepository;
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
