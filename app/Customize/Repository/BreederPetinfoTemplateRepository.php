<?php

namespace Customize\Repository;

use Customize\Entity\BreederPetinfoTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederPetinfoTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederPetinfoTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederPetinfoTemplate[]    findAll()
 * @method BreederPetinfoTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederPetinfoTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederPetinfoTemplate::class);
    }

    // /**
    //  * @return BreederPetinfoTemplate[] Returns an array of BreederPetinfoTemplate objects
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
    public function findOneBySomeField($value): ?BreederPetinfoTemplate
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
