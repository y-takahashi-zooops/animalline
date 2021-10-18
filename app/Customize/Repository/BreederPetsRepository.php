<?php

namespace Customize\Repository;

use Customize\Config\AnilineConf;
use Customize\Entity\BreederPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Util\StringUtil;

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

    /**
     * Search breederPets
     *
     * @param array $order
     * @return array
     */
    public function filterBreederPetsAdmin(array $criteria, array $order): array
    {
        $qb = $this->createQueryBuilder('bp');
        if (!empty($criteria['pet_kind']) && StringUtil::isNotBlank($criteria['pet_kind'])) {
            $qb
                ->andWhere($qb->expr()->in('bp.pet_kind', ':pet_kind'))
                ->setParameter('pet_kind', $criteria['pet_kind']);
        }
        if (!empty($criteria['breed_type']) && StringUtil::isNotBlank($criteria['breed_type'])) {
            $qb
                ->andWhere($qb->expr()->in('bp.BreedsType', ':BreedsType'))
                ->setParameter('BreedsType', $criteria['breed_type']);
        }
        if (!empty($criteria['public_status']) && StringUtil::isNotBlank($criteria['public_status'])) {
            if ($criteria['public_status'] == 1) {
                $qb
                    ->andWhere($qb->expr()->in('bp.is_active', ':is_active'))
                    ->setParameter('is_active', AnilineConf::ANILINE_IS_ACTIVE_PRIVATE);
            }
            if ($criteria['public_status'] == 2) {
                $qb
                    ->andWhere($qb->expr()->in('bp.is_active', ':is_active'))
                    ->setParameter('is_active', AnilineConf::ANILINE_IS_ACTIVE_PUBLIC);
            }
            if ($criteria['public_status'] == 3) {
                $qb
                    ->andWhere($qb->expr()->in('bp.dna_check_result', ':dna_check_result'))
                    ->setParameter('dna_check_result', AnilineConf::DNA_CHECK_RESULT_CHECKING);
            }
            if ($criteria['public_status'] == 4) {
                $qb
                    ->andWhere($qb->expr()->in('bp.dna_check_result', ':dna_check_result'))
                    ->setParameter('dna_check_result', AnilineConf::DNA_CHECK_RESULT_CHECK_OK);
            }
            if ($criteria['public_status'] == 5) {
                $qb
                    ->andWhere($qb->expr()->in('bp.dna_check_result', ':dna_check_result'))
                    ->setParameter('dna_check_result', AnilineConf::DNA_CHECK_RESULT_CHECK_NG);
            }
        }

        return $qb->leftJoin('Customize\Entity\DnaCheckStatus', 'dna', 'WITH', 'bp.id = dna.pet_id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'bp.BreedsType = b.id')
            ->select('bp', 'dna', 'b.breeds_name')
            ->orderBy('bp.' . $order['field'], $order['direction'])
            ->getQuery()
            ->getScalarResult();
    }
}
