<?php

namespace Customize\Repository;

use Customize\Entity\ShippingSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingSchedule[]    findAll()
 * @method ShippingSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingSchedule::class);
    }

    // /**
    //  * @return ShippingSchedule[] Returns an array of ShippingSchedule objects
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
    public function findOneBySomeField($value): ?ShippingSchedule
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
