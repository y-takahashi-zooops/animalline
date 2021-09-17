<?php

namespace Customize\Repository;

use Customize\Entity\DnaCheckStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DnaCheckStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaCheckStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaCheckStatus[]    findAll()
 * @method DnaCheckStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaCheckStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaCheckStatus::class);
    }
}
