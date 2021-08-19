<?php

namespace Customize\Repository;

use Customize\Entity\ConservationPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConservationPets|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConservationPets|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConservationPets[]    findAll()
 * @method ConservationPets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationPetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationPets::class);
    }

    public function incrementCount(ConservationPets $entity)
    {
        return $this
            ->createQueryBuilder('e')
            ->update()
            ->set('e.favorite_count', 'case when e.favorite_count is null then 1 else e.favorite_count + 1 end')
            ->where('e.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    public function decrementCount(ConservationPets $entity)
    {
        return $this
            ->createQueryBuilder('e')
            ->update()
            ->set('e.favorite_count', 'case when e.favorite_count > 0 then e.favorite_count - 1 else 0 end')
            ->where('e.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @return ConservationPets[] Returns an array of ConservationPets objects
     */

    public function findByFavoriteCount()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.favorite_count > 0')
            ->orderBy('a.favorite_count', 'DESC')
            ->getQuery()
            ->getResult();
    }

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
