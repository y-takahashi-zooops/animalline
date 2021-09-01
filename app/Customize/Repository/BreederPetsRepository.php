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
     * @return BreederPets[] Returns an array of BreederPets objects
     */

    public function findByFavoriteCount(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.favorite_count > 0')
            ->orderBy('a.favorite_count', 'DESC')
            ->getQuery()
            ->getResult();
    }

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

    /**
     * Search pet with examination_status and pet_name
     *
     * @param array $criteria
     * @param array $order
     * @return array
     */
    public function filterBreederPetAdmin(array $criteria, array $order)
    {
        $qb = $this->createQueryBuilder('b');
        if (isset($criteria['pet_name']) && StringUtil::isNotBlank($criteria['pet_name'])) {
            $qb
                ->andWhere('b.pet_name LIKE :pet_name')
                ->setParameter('pet_name', '%' . $criteria['pet_name'] . '%');
        }
        if (!empty($criteria['examination_status']) && count($criteria['examination_status'])) {
            $qb
                ->andWhere($qb->expr()->in('b.examination_status', ':examination_status'))
                ->setParameter('examination_status', $criteria['examination_status']);
        }
        return $qb->orderBy('b.' . $order['field'], $order['direction'])
            ->getQuery()
            ->getResult();

    }
}
