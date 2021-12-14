<?php

namespace Customize\Repository;

use Customize\Entity\BenefitsStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BenefitsStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method BenefitsStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method BenefitsStatus[]    findAll()
 * @method BenefitsStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BenefitsStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BenefitsStatus::class);
    }

    // /**
    //  * @return BenefitsStatus[] Returns an array of BenefitsStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BenefitsStatus
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
