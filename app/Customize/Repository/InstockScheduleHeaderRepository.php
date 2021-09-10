<?php

namespace Customize\Repository;

use Customize\Entity\InstockScheduleHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InstockScheduleHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstockScheduleHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstockScheduleHeader[]    findAll()
 * @method InstockScheduleHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstockScheduleHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstockScheduleHeader::class);
    }

    // /**
    //  * @return InstockScheduleHeader[] Returns an array of InstockScheduleHeader objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InstockScheduleHeader
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
