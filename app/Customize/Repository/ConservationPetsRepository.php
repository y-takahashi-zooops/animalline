<?php

namespace Customize\Repository;

use Customize\Entity\ConservationPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdoptionPets|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdoptionPets|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdoptionPets[]    findAll()
 * @method AdoptionPets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationPetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationPets::class);
    }

    // /**
    //  * @return ConservationPets[] Returns an array of ConservationPets objects
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
    public function findOneBySomeField($value): ?ConservationPets
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
