<?php

namespace Customize\Repository;

use Customize\Entity\BenefitsStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BenefitsStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method BenefitsStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method BenefitsStatus[]    findAll()
 * @method BenefitsStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BenefitsStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BenefitsStatus::class);
    }

    /**
     * Admin Benefit filter
     *
     * @param array $criteria
     * @return array
     */
    public function filterBenefitAdmin(array $criteria): array
    {
        $siteType = $criteria['site_type'] ?? '';
        $checkStatus = $criteria['check_status'] ?? '';
        $createDateFrom = $criteria['kit_regist_date_from'] ?? '';
        $createDateTo = $criteria['create_date_to'] ?? '';
        $benefitsShippingDateFrom = $criteria['benefits_shipping_date_from'] ?? '';
        $benefitsShippingDateTo = $criteria['benefits_shipping_date_to'] ?? '';
        $fromTime = ' 00:00:00';
        $toTime = ' 23:59:59';

        $query = $this->createQueryBuilder('bf');
        if ($siteType) {
            $query->where('bf.site_type = :site_type')
                ->setParameters(['site_type' => $siteType]);
        }

        if (!empty($checkStatus)) {
            $query->andWhere('bf.shipping_status = :shipping_status')
                ->setParameter(':shipping_status', $checkStatus);
        }

        if (!empty($createDateFrom)) {
            $fromDatetime = $createDateFrom . $fromTime;
            $query
                ->andWhere("bf.create_date >= '$fromDatetime'");
        }

        if (!empty($createDateTo)) {
            $toDatetime = $createDateTo . $toTime;
            $query
                ->andWhere("bf.create_date <= '$toDatetime'");
        }

        if (!empty($benefitsShippingDateFrom)) {
            $fromDatetime = $benefitsShippingDateFrom . $fromTime;
            $query
                ->andWhere("bf.benefits_shipping_date >= '$fromDatetime'");
        }
        if (!empty($benefitsShippingDateTo)) {
            $toDatetime = $benefitsShippingDateTo . $toTime;
            $query
                ->andWhere("bf.benefits_shipping_date <= '$toDatetime'");
        }

        $result = $query->getQuery()->getArrayResult();

        // order by update_date > dna_id desc
        usort(
            $result,
            function ($x, $y) {
                return [$y['update_date']->getTimestamp(), $y['id']]
                    <=>
                    [$x['update_date']->getTimestamp(), $x['id']];
            }
        );

        return $result;
    }
}
