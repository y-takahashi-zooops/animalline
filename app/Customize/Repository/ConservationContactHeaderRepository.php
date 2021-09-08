<?php

namespace Customize\Repository;

use Customize\Entity\ConservationContactHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConservationContactHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConservationContactHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConservationContactHeader[]    findAll()
 * @method ConservationContactHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationContactHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationContactHeader::class);
    }

    // /**
    //  * @return ConservationContactHeader[] Returns an array of ConservationContactHeader objects
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
    public function findOneBySomeField($value): ?ConservationContactHeader
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
