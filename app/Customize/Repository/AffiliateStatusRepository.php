<?php

namespace Customize\Repository;

use Customize\Entity\AffiliateStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AffiliateStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method AffiliateStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method AffiliateStatus[]    findAll()
 * @method AffiliateStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffiliateStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffiliateStatus::class);
    }
}
