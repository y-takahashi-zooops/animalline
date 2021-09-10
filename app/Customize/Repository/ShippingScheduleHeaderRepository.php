<?php

namespace Customize\Repository;

use Customize\Entity\ShippingScheduleHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingScheduleHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingScheduleHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingScheduleHeader[]    findAll()
 * @method ShippingScheduleHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingScheduleHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingScheduleHeader::class);
    }

    // /**
    //  * @return ShippingScheduleHeader[] Returns an array of ShippingScheduleHeader objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShippingScheduleHeader
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
