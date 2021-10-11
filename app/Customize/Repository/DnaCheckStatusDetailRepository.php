<?php

namespace Customize\Repository;

use Customize\Entity\DnaCheckStatusDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DnaCheckStatusDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaCheckStatusDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaCheckStatusDetail[]    findAll()
 * @method DnaCheckStatusDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaCheckStatusDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaCheckStatusDetail::class);
    }

    // /**
    //  * @return DnaCheckStatusDetail[] Returns an array of DnaCheckStatusDetail objects
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
    public function findOneBySomeField($value): ?DnaCheckStatusDetail
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
