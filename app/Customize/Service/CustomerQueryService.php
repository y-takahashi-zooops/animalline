<?php

namespace Customize\Service;

use Carbon\Carbon;
use Customize\Repository\MonthlyInvoiceRepository;
use Symfony\Component\HttpFoundation\Request;

class CustomerQueryService
{
    /**
     * @var MonthlyInvoiceRepository
     */
    protected $monthlyInvoiceRepository;

    /**
     * CustomerQueryService constructor.
     *
     * @param MonthlyInvoiceRepository $monthlyInvoiceRepository
     */
    public function __construct(
        MonthlyInvoiceRepository $monthlyInvoiceRepository
    ) {
        $this->monthlyInvoiceRepository = $monthlyInvoiceRepository;
    }

    /**
     * get breeds have pet
     *
     * @param Request $request
     */
    public function getMonthlyInvoice(Request $request)
    {
        $yearMonth = $request->get('year') ? $request->get('year') . sprintf('%02d', $request->get('month')) : Carbon::now()->format('Ym');

        $query = $this->monthlyInvoiceRepository->createQueryBuilder('mi')
            ->leftJoin('Eccube\Entity\Customer', 'c', 'WITH', 'mi.Customer = c.id')
            ->where('mi.site_category = :site_category')
            ->andWhere('mi.yearmonth = :yearMonth')
            ->setParameters([
                'site_category' => $request->get('type') ?? 1,
                'yearMonth' => $yearMonth
            ])
            ->select('mi as monthlyInvoice', 'c.name01', 'c.name02');
            return $query->getQuery()
            ->getArrayResult();
    }
}
