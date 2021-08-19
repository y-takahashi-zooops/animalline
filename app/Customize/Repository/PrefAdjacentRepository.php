<?php

namespace Customize\Repository;

use Customize\Entity\PrefAdjacent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PrefAdjacent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrefAdjacent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrefAdjacent[]    findAll()
 * @method PrefAdjacent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrefAdjacentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrefAdjacent::class);
    }
}
