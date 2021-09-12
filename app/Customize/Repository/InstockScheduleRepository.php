<?php

namespace Customize\Repository;

use Customize\Entity\InstockSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InstockSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstockSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstockSchedule[]    findAll()
 * @method InstockSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstockScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstockSchedule::class);
    }

    // /**
    //  * @return InstockSchedule[] Returns an array of InstockSchedule objects
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
    public function findOneBySomeField($value): ?InstockSchedule
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
