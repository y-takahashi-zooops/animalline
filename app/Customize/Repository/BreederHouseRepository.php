<?php

namespace Customize\Repository;

use Customize\Entity\BreederHouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederHouse|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederHouse|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederHouse[]    findAll()
 * @method BreederHouse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederHouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederHouse::class);
    }
}
