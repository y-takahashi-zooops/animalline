<?php

namespace Customize\Repository;

use Customize\Entity\StockWasteReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StockWasteReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockWasteReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockWasteReason[]    findAll()
 * @method StockWasteReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockWasteReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockWasteReason::class);
    }

    // /**
    //  * @return StockWasteReason[] Returns an array of StockWasteReason objects
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
    public function findOneBySomeField($value): ?StockWasteReason
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
