<?php

namespace Customize\Repository;

use Customize\Entity\BleederPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BleederPets|null find($id, $lockMode = null, $lockVersion = null)
 * @method BleederPets|null findOneBy(array $criteria, array $orderBy = null)
 * @method BleederPets[]    findAll()
 * @method BleederPets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BleederPetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BleederPets::class);
    }

    // /**
    //  * @return BleederPets[] Returns an array of BleederPets objects
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
    public function findOneBySomeField($value): ?BleederPets
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
