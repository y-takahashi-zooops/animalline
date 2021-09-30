<?php

namespace Customize\Service;

use Customize\Repository\StockWasteRepository;

class GetListWasteQueryService
{
    /**
     * @var StockWasteRepository
     */
    protected $stockWasteRepository;

    /**
     * MailService constructor.
     *
     * @param StockWasteRepository $stockWasteRepository
     */
    public function __construct(
        StockWasteRepository $stockWasteRepository
    ) {
        $this->stockWasteRepository = $stockWasteRepository;
    }


    /**
     * Search waste
     *
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|int|mixed|string
     * @throws \Exception
     */
    public function search($dateFrom = null, $dateTo = null)
    {
        $result = $this->stockWasteRepository->createQueryBuilder('w');
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
                return $result = [];
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
                return $result = [];
            }
        }

        return $result->addOrderBy('w.waste_date', 'DESC')->getQuery()->getResult();
    }
}
