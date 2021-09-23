<?php

namespace Customize\Service;

use Customize\Repository\InstockScheduleHeaderRepository;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\MailHistoryRepository;
use Eccube\Repository\MailTemplateRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ListInstockQueryService
{
    /**
     * @var InstockScheduleHeaderRepository
     */
    protected $instockScheduleHeaderRepository;

    /**
     * MailService constructor.
     *
     * @param InstockScheduleHeaderRepository $instockScheduleHeaderRepository
     */
    public function __construct(
        InstockScheduleHeaderRepository $instockScheduleHeaderRepository
    )
    {
        $this->instockScheduleHeaderRepository = $instockScheduleHeaderRepository;
    }

    public function search($dates)
    {
        $result = $this->instockScheduleHeaderRepository->createQueryBuilder('i');
            $orderDate = $dates['order_date'];
            $scheduleDate = $dates['arrival_date_schedule'];
            if ($orderDate['year']) {
                $result->where('YEAR(i.order_date) = :year')
                    ->setParameter('year', $orderDate['year'])
                    ->getQuery()
                    ->getResult();
            }
            return $result;
    }
}
