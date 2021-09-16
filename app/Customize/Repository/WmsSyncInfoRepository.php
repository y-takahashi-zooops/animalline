<?php

namespace Customize\Repository;

use Customize\Entity\WmsSyncInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WmsSyncInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method WmsSyncInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method WmsSyncInfo[]    findAll()
 * @method WmsSyncInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WmsSyncInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WmsSyncInfo::class);
    }

    // /**
    //  * @return WmsSyncInfo[] Returns an array of WmsSyncInfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WmsSyncInfo
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
