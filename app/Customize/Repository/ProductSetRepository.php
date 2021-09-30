<?php

namespace Customize\Repository;

use Customize\Entity\ProductSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductSet[]    findAll()
 * @method ProductSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductSet::class);
    }

    // /**
    //  * @return ProductSet[] Returns an array of ProductSet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductSet
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
