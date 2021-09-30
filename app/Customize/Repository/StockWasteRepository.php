<?php

namespace Customize\Repository;

use Customize\Entity\StockWaste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StockWaste|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockWaste|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockWaste[]    findAll()
 * @method StockWaste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockWasteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockWaste::class);
    }

    // /**
    //  * @return StockWaste[] Returns an array of StockWaste objects
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
    public function findOneBySomeField($value): ?StockWaste
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
