<?php

namespace Customize\Repository;

use Customize\Entity\DnaSalesDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DnaSalesDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaSalesDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaSalesDetail[]    findAll()
 * @method DnaSalesDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaSalesDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaSalesDetail::class);
    }

    // /**
    //  * @return DnaSalesDetail[] Returns an array of DnaSalesDetail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DnaSalesDetail
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
