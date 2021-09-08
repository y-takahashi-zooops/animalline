<?php

namespace Customize\Repository;

use Customize\Entity\BreederEvaluations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederEvaluations|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederEvaluations|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederEvaluations[]    findAll()
 * @method BreederEvaluations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederEvaluationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederEvaluations::class);
    }

    // /**
    //  * @return BreederEvaluations[] Returns an array of BreederEvaluations objects
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
    public function findOneBySomeField($value): ?BreederEvaluations
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
