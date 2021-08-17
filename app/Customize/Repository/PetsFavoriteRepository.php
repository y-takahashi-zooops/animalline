<?php

namespace Customize\Repository;

use Customize\Entity\PetsFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetsFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetsFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetsFavorite[]    findAll()
 * @method PetsFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetsFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetsFavorite::class);
    }

    // /**
    //  * @return PetsFavorite[] Returns an array of PetsFavorite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PetsFavorite
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
