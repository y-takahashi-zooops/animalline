<?php

namespace Customize\Repository;

use Customize\Entity\ItemWaste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemWaste|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemWaste|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemWaste[]    findAll()
 * @method ItemWaste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemWasteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemWaste::class);
    }

    // /**
    //  * @return ItemWaste[] Returns an array of ItemWaste objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemWaste
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
