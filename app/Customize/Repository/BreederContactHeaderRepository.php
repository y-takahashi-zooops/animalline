<?php

namespace Customize\Repository;

use Customize\Entity\BreederContactHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederContactHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederContactHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederContactHeader[]    findAll()
 * @method BreederContactHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederContactHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederContactHeader::class);
    }
}
