<?php

namespace Customize\Repository;

use Customize\Entity\Breeders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Util\StringUtil;

/**
 * @method Breeders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Breeders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Breeders[]    findAll()
 * @method Breeders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreedersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Breeders::class);
    }

    /**
     * Search breeder with examination_status and breeder_name
     *
     * @param array $criteria
     * @param array $order
     * @return array
     */
    public function filterBreederAdmin(array $criteria, array $order): array
    {
        $qb = $this->createQueryBuilder('b');
        if (isset($criteria['breeder_name']) && StringUtil::isNotBlank($criteria['breeder_name'])) {
            $qb
                ->andWhere('b.breeder_name LIKE :breeder_name')
                ->setParameter('breeder_name', '%' . $criteria['breeder_name'] . '%');
        }
        if (!empty($criteria['examination_status']) && count($criteria['examination_status'])) {
            $qb
                ->andWhere($qb->expr()->in('b.examination_status', ':examination_status'))
                ->setParameter('examination_status', $criteria['examination_status']);
        }
        if (isset($criteria['create_date']) && StringUtil::isNotBlank($criteria['create_date'])) {
            $begin_datetime = $criteria['create_date'] . ' 00:00:00';
            $end_datetime = $criteria['create_date'] . ' 23:59:59';
            $qb
                ->andWhere("b.create_date >= '$begin_datetime' and b.create_date <= '$end_datetime'");
        }
        if (isset($criteria['update_date']) && StringUtil::isNotBlank($criteria['update_date'])) {
            $begin_datetime = $criteria['update_date'] . ' 00:00:00';
            $end_datetime = $criteria['update_date'] . ' 23:59:59';
            $qb
                ->andWhere("b.update_date >= '$begin_datetime' and b.update_date <= '$end_datetime'");
        }

        return $qb->orderBy('b.' . $order['field'], $order['direction'])
            ->getQuery()
            ->getResult();
    }
}
