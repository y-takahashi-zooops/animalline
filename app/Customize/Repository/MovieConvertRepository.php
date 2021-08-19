<?php

namespace Customize\Repository;

use Customize\Entity\MovieConvert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MovieConvert|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovieConvert|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovieConvert[]    findAll()
 * @method MovieConvert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieConvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieConvert::class);
    }

    // /**
    //  * @return MovieConvert[] Returns an array of MovieConvert objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MovieConvert
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
