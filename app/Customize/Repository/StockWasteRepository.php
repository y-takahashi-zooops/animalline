<?php

namespace Customize\Repository;

use Customize\Entity\StockWaste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method StockWaste|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockWaste|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockWaste[]    findAll()
 * @method StockWaste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockWasteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockWaste::class);
    }

    /**
     * Search waste admin
     *
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|int|mixed|string
     * @throws Exception
     */
    public function search($dateFrom = null, $dateTo = null)
    {
        $result = $this->createQueryBuilder('w');
        if ($dateFrom['yearFrom']) {
            $fromTimeYear = new \DateTime($dateFrom['yearFrom'] . '-01' . '-01');
            $result->where('w.waste_date >= :fromYear')
                ->setParameter(':fromYear', $fromTimeYear);
            if ($dateFrom['monthFrom']) {
                $fromTimeMonth = new \DateTime($dateFrom['yearFrom'] . '-' . $dateFrom['monthFrom'] . '-01');
                $result = $result->andWhere('w.waste_date >= :fromMonth')
                    ->setParameter(':fromMonth', $fromTimeMonth);
            }
        } else {
            if ($dateFrom['monthFrom']) {
                return [];
            }
        }
        if ($dateTo['yearTo']) {
            $toTimeYear = new \DateTime($dateTo['yearTo'] . '-12' . '-31');
            $result->andWhere('w.waste_date <= :toYear')
                ->setParameter(':toYear', $toTimeYear);
            if ($dateTo['monthTo']) {
                $toTimeMonth = new \DateTime($dateTo['yearTo'] . '-' . $dateTo['monthTo'] . '-31');
                $result = $result->andWhere('w.waste_date <= :toMonth')
                    ->setParameter(':toMonth', $toTimeMonth);
            }
        } else {
            if ($dateTo['monthTo']) {
                return [];
            }
        }

        return $result->orderBy('w.update_date', 'DESC')->getQuery()->getResult();
    }
}
