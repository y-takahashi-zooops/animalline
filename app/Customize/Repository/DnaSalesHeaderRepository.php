<?php

namespace Customize\Repository;

use Customize\Entity\DnaSalesHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DnaSalesHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaSalesHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaSalesHeader[]    findAll()
 * @method DnaSalesHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaSalesHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaSalesHeader::class);
    }

    // /**
    //  * @return DnaSalesHeader[] Returns an array of DnaSalesHeader objects
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
    public function findOneBySomeField($value): ?DnaSalesHeader
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
