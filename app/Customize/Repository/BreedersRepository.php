<?php

namespace Customize\Repository;

use Customize\Entity\Breeders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Breeders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Breeders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Breeders[]    findAll()
 * @method Breeders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreedersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Breeders::class);
    }

    // /**
    //  * @return Breeders[] Returns an array of Breeders objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Breeders
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
