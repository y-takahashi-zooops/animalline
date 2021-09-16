<?php

namespace Customize\Repository;

use Customize\Entity\ReturnScheduleHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReturnScheduleHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReturnScheduleHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReturnScheduleHeader[]    findAll()
 * @method ReturnScheduleHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReturnScheduleHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReturnScheduleHeader::class);
    }

    // /**
    //  * @return ReturnScheduleHeader[] Returns an array of ReturnScheduleHeader objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReturnScheduleHeader
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
