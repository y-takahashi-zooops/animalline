<?php

namespace Customize\Repository;

use Customize\Entity\ProductMaker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductMaker|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductMaker|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductMaker[]    findAll()
 * @method ProductMaker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductMakerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductMaker::class);
    }

    // /**
    //  * @return ProductMaker[] Returns an array of ProductMaker objects
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
    public function findOneBySomeField($value): ?ProductMaker
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
