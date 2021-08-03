<?php

namespace Customize\Repository;

use Customize\Entity\AdoptionPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdoptionPets|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdoptionPets|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdoptionPets[]    findAll()
 * @method AdoptionPets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdoptionPetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdoptionPets::class);
    }

    // /**
    //  * @return AdoptionPets[] Returns an array of AdoptionPets objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AdoptionPets
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
