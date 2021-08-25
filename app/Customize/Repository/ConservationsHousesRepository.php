<?php

namespace Customize\Repository;

use Customize\Entity\ConservationsHouses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConservationsHouses|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConservationsHouses|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConservationsHouses[]    findAll()
 * @method ConservationsHouses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationsHousesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationsHouses::class);
    }

    // /**
    //  * @return ConservationsHouses[] Returns an array of ConservationsHouses objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConservationsHouses
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
