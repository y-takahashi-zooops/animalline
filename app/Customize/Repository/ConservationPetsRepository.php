<?php

namespace Customize\Repository;

use Customize\Entity\ConservationPets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Customize\Config\AnilineConf;
use Eccube\Util\StringUtil;

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
     * @param ConservationPets $entity
     * @return int|mixed|string
     */
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

    /**
     * Decrement favorite count of pet
     *
     * @param ConservationPets $entity
     * @return int|mixed|string
     */
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
     * Search conservationPets
     *
     * @param array $criteria
     * @param array $order
     * @return array
     */
    public function filterConservationPetsAdmin(array $criteria, array $order): array
    {
        $qb = $this->createQueryBuilder('cp');
        if (!empty($criteria['pet_kind']) && StringUtil::isNotBlank($criteria['pet_kind'])) {
            $qb
                ->andWhere($qb->expr()->in('cp.pet_kind', ':pet_kind'))
                ->setParameter('pet_kind', $criteria['pet_kind']);
        }
        if (!empty($criteria['breed_type']) && StringUtil::isNotBlank($criteria['breed_type'])) {
            $qb
                ->andWhere($qb->expr()->in('cp.BreedsType', ':BreedsType'))
                ->setParameter('BreedsType', $criteria['breed_type']);
        }
        if (!empty($criteria['public_status']) && StringUtil::isNotBlank($criteria['public_status'])) {
            if ($criteria['public_status'] == 1) {
                $qb
                    ->andWhere($qb->expr()->in('cp.is_active', ':release_status'))
                    ->setParameter('release_status', AnilineConf::RELEASE_STATUS_PRIVATE);
            }
            if ($criteria['public_status'] == 2) {
                $qb
                    ->andWhere($qb->expr()->in('cp.is_active', ':release_status'))
                    ->setParameter('release_status', AnilineConf::RELEASE_STATUS_PUBLIC);
            }
            if ($criteria['public_status'] == 3) {
                $qb
                    ->andWhere($qb->expr()->in('cp.dna_check_result', ':dna_check_result'))
                    ->setParameter('dna_check_result', AnilineConf::DNA_CHECK_RESULT_CHECKING);
            }
            if ($criteria['public_status'] == 4) {
                $qb
                    ->andWhere($qb->expr()->in('cp.dna_check_result', ':dna_check_result'))
                    ->setParameter('dna_check_result', AnilineConf::DNA_CHECK_RESULT_CHECK_OK);
            }
            if ($criteria['public_status'] == 5) {
                $qb
                    ->andWhere($qb->expr()->in('cp.dna_check_result', ':dna_check_result'))
                    ->setParameter('dna_check_result', AnilineConf::DNA_CHECK_RESULT_CHECK_NG);
            }
        }
        if (!empty($criteria['create_date']) && StringUtil::isNotBlank($criteria['create_date'])) {
            $begin_datetime = $criteria['create_date'] . ' 00:00:00';
            $end_datetime = $criteria['create_date'] . ' 23:59:59';
            $qb
                ->andWhere("cp.create_date >= '$begin_datetime' and cp.create_date <= '$end_datetime'");
        }
        if (!empty($criteria['update_date']) && StringUtil::isNotBlank($criteria['update_date'])) {
            $begin_datetime = $criteria['update_date'] . ' 00:00:00';
            $end_datetime = $criteria['update_date'] . ' 23:59:59';
            $qb
                ->andWhere("cp.update_date >= '$begin_datetime' and cp.update_date <= '$end_datetime'");
        }
        if (!empty($criteria['holder_name']) && StringUtil::isNotBlank($criteria['holder_name'])) {
            $qb->join('cp.Conservation', 'cn')
                ->andWhere('cn.organization_name LIKE :organization_name')
                ->setParameter('organization_name', '%' . $criteria['holder_name'] . '%');
        }
        if (!empty($criteria['create_date_start']) && StringUtil::isNotBlank($criteria['create_date_start'])) {
            $begin_datetime = $criteria['create_date_start'] . ' 00:00:00';
            $qb
                ->andWhere("cp.create_date >= '$begin_datetime'");
        }
        if (!empty($criteria['create_date_end']) && StringUtil::isNotBlank($criteria['create_date_end'])) {
            $end_datetime = $criteria['create_date_end'] . ' 23:59:59';
            $qb
                ->andWhere("cp.create_date <= '$end_datetime'");
        }
        if (!empty($criteria['update_date_start']) && StringUtil::isNotBlank($criteria['update_date_start'])) {
            $begin_datetime = $criteria['update_date_start'] . ' 00:00:00';
            $qb
                ->andWhere("cp.update_date >= '$begin_datetime'");
        }
        if (!empty($criteria['update_date_end']) && StringUtil::isNotBlank($criteria['update_date_end'])) {
            $end_datetime = $criteria['update_date_end'] . ' 23:59:59';
            $qb
                ->andWhere("cp.update_date <= '$end_datetime'");
        }

        return $qb->leftJoin('Customize\Entity\DnaCheckStatus', 'dna', 'WITH', 'cp.id = dna.pet_id')
            ->leftJoin('Customize\Entity\Breeds', 'b', 'WITH', 'cp.BreedsType = b.id')
            ->leftJoin('Customize\Entity\Conservations', 'c', 'WITH', 'c.id = cp.Conservation')
            ->select('cp', 'dna', 'b.breeds_name', 'c.owner_name')
            ->orderBy('cp.' . $order['field'], $order['direction'])
            ->getQuery()
            ->getScalarResult();
    }
}
