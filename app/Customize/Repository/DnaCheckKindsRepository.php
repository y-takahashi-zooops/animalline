<?php

namespace Customize\Repository;

use Customize\Entity\DnaCheckKinds;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DnaCheckKinds|null find($id, $lockMode = null, $lockVersion = null)
 * @method DnaCheckKinds|null findOneBy(array $criteria, array $orderBy = null)
 * @method DnaCheckKinds[]    findAll()
 * @method DnaCheckKinds[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DnaCheckKindsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DnaCheckKinds::class);
    }

    // /**
    //  * @return DnaCheckKinds[] Returns an array of DnaCheckKinds objects
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
    public function findOneBySomeField($value): ?DnaCheckKinds
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
