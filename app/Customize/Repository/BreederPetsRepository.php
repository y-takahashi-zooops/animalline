<?php

namespace Customize\Repository;

use Customize\Entity\BreederPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederPets|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederPets|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederPets[]    findAll()
 * @method BreederPets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederPetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederPets::class);
    }

    /**
     * Get list favorite pet decrement
     *
     * @return array
     */
    public function findByFavoriteCount(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.favorite_count > 0')
            ->orderBy('a.favorite_count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Increment favorite count of pet
     *
     * @param BreederPets $entity
     * @return int|mixed|string
     */
    public function incrementCount(BreederPets $entity)
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

    /**
     * Decrement favorite count of pet
     *
     * @param BreederPets $entity
     * @return int|mixed|string
     */
    public function decrementCount(BreederPets $entity)
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
}
