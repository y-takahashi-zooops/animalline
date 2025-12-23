<?php

/*
 * Copyright(c) 2022 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Repository;

use Doctrine\ORM\QueryBuilder;
use Eccube\Repository\AbstractRepository;
use Eccube\Util\StringUtil;
use Plugin\GmoPaymentGateway4\Entity\GmoFraudDetection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GmoFraudDetectionRepository
 */
class GmoFraudDetectionRepository extends AbstractRepository
{
    /**
     * GmoFraudDetectionRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmoFraudDetection::class);
    }

    /**
     * 検索条件をセットしたQueryBuilderを返す
     *
     * @param array $searchData
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchData(array $searchData)
    {
        $qb = $this->createQueryBuilder('fd');

        // IPアドレス
        if (isset($searchData['ip_address']) &&
            StringUtil::isNotBlank($searchData['ip_address'])) {
            $ip = $searchData['ip_address'];
            $qb
                ->andWhere('fd.ip_address LIKE :ip_address')
                ->setParameter('ip_address', '%' . $ip . '%');
        }

        // 発生日付
        if (!empty($searchData['occur_time_start']) &&
            $searchData['occur_time_start']) {
            $qb
                ->andWhere('fd.occur_time >= :occur_time_start')
                ->setParameter('occur_time_start',
                               $searchData['occur_time_start']);
        }
        if (!empty($searchData['occur_time_end']) &&
            $searchData['occur_time_end']) {
            $date = clone $searchData['occur_time_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('fd.occur_time < :occur_time_end')
                ->setParameter('occur_time_end', $date);
        }

        // エラー回数
        if (!empty($searchData['error_count_start']) &&
            $searchData['error_count_start']) {
            $qb
                ->andWhere('fd.error_count >= :error_count_start')
                ->setParameter('error_count_start',
                               $searchData['error_count_start']);
        }
        if (!empty($searchData['error_count_end']) &&
            $searchData['error_count_end']) {
            $qb
                ->andWhere('fd.error_count <= :error_count_end')
                ->setParameter('error_count_end',
                               $searchData['error_count_end']);
        }

        // 作成日付
        if (!empty($searchData['create_date_start']) &&
            $searchData['create_date_start']) {
            $qb
                ->andWhere('fd.create_date >= :create_date_start')
                ->setParameter('create_date_start',
                               $searchData['create_date_start']);
        }
        if (!empty($searchData['create_date_end']) &&
            $searchData['create_date_end']) {
            $date = clone $searchData['create_date_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('fd.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        }

        // 更新日付
        if (!empty($searchData['update_date_start']) &&
            $searchData['update_date_start']) {
            $qb
                ->andWhere('fd.update_date >= :update_date_start')
                ->setParameter('update_date_start',
                               $searchData['update_date_start']);
        }
        if (!empty($searchData['update_date_end']) &&
            $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('fd.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // Order By
        $qb->addOrderBy('fd.occur_time', 'DESC');

        return $qb;
    }
}
