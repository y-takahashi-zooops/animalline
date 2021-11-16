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

        $fromTime = ' 00:00:00';
        $toTime = ' 23:59:59';
        if (isset($criteria['create_date_from']) && StringUtil::isNotBlank($criteria['create_date_from'])) {
            $fromDatetime = $criteria['create_date_from'] . $fromTime;
            $qb
                ->andWhere("b.create_date >= '$fromDatetime'");
        }
        if (isset($criteria['create_date_to']) && StringUtil::isNotBlank($criteria['create_date_to'])) {
            $toDatetime = $criteria['create_date_to'] . $toTime;
            $qb
                ->andWhere("b.create_date <= '$toDatetime'");
        }
        if (isset($criteria['update_date_from']) && StringUtil::isNotBlank($criteria['update_date_from'])) {
            $fromDatetime = $criteria['update_date_from'] . $fromTime;
            $qb
                ->andWhere("b.update_date >= '$fromDatetime'");
        }
        if (isset($criteria['update_date_from']) && StringUtil::isNotBlank($criteria['update_date_from'])) {
            $toDatetime = $criteria['update_date_from'] . $toDatetime;
            $qb
                ->andWhere("b.update_date <= '$toDatetime'");
        }

        return $qb->orderBy('b.' . $order['field'], $order['direction'])
            ->getQuery()
            ->getResult();
    }
}
