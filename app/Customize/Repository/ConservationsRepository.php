<?php

namespace Customize\Repository;

use Customize\Entity\Conservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Util\StringUtil;
use Customize\Config\AnilineConf;

/**
 * @method Conservations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conservations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conservations[]    findAll()
 * @method Conservations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conservations::class);
    }

    /**
     * Search conservation with examination_status and organization_name
     *
     * @param  array $request
     * @return array
     */
    public function searchConservations($request)
    {
        $qb = $this->createQueryBuilder('c');
        if (isset($request['organization_name']) && !empty($request['organization_name'])) {
            $qb->andWhere('c.organization_name LIKE :organization_name')
                ->setParameter('organization_name', '%' . $request['organization_name'] . '%');
        }

        if (isset($request['examination_status'])) {
            switch ($request['examination_status']) {
                case 1:
                    break;
                case 2:
                    $qb->andWhere('c.examination_status IN (:examination_status)')
                        ->setParameter('examination_status', [
                            AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK,
                            AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG
                        ]);
                    break;
                case 3:
                    $qb->andWhere('c.examination_status = :examination_status')
                        ->setParameter('examination_status', AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK);
                    break;
            }
        }

        $orderField = isset($request['field']) ? $request['field'] : 'create_date';
        $direction = isset($request['direction']) ? $request['direction'] : 'DESC';
        return $qb->orderBy('c.' . $orderField, $direction)
            ->getQuery()
            ->getResult();
    }
}
