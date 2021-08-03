<?php

namespace Customize\Repository;

use Customize\Entity\CoatColors;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CoatColors|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoatColors|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoatColors[]    findAll()
 * @method CoatColors[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoatColorsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoatColors::class);
    }

    // /**
    //  * @return CoatColors[] Returns an array of CoatColors objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CoatColors
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
