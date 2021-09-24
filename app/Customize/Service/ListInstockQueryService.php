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
        if ($orderDate) {
            if ($orderDate['year'] and $orderDate['month'] and $orderDate['day']) {
                $orderDateInput = new \DateTime($orderDate['year'] . '-' . $orderDate['month'] . '-' . $orderDate['day']);
                $result = $result->where('i.order_date = :orderDateInput')
                    ->setParameter('orderDateInput', $orderDateInput);
            }
            if ($orderDate['year'] and $orderDate['month'] and !$orderDate['day']) {
                $fromTime = new \DateTime($orderDate['year'] . '-' . $orderDate['month'] . '-01');
                $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next month');
                $result = $result->where('i.order_date >= :fromTime')
                    ->andWhere('i.order_date < :toTime')
                    ->setParameter('fromTime', $fromTime)
                    ->setParameter('toTime', $toTime);
            }
            if ($orderDate['year'] and !$orderDate['month']) {
                $fromTime = new \DateTime($orderDate['year'] . '-01' . '-01');
                $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next year');
                $result = $result->where('i.order_date >= :fromTime')
                    ->andWhere('i.order_date < :toTime')
                    ->setParameter('fromTime', $fromTime)
                    ->setParameter('toTime', $toTime);
            }
        }
        if ($scheduleDate) {
            if ($scheduleDate['year'] and $scheduleDate['month'] and $scheduleDate['day']) {
                $scheduleDateInput = new \DateTime($scheduleDate['year'] . '-' . $scheduleDate['month'] . '-' . $scheduleDate['day']);
                $orderDate ? $result = $result->andWhere('i.order_date = :scheduleDateInput')
                             ->setParameter('scheduleDateInput', $scheduleDateInput)
                            : $result = $result->where('i.order_date = :scheduleDateInput')
                            ->setParameter('scheduleDateInput', $scheduleDateInput);
            }
            if ($scheduleDate['year'] and $scheduleDate['month'] and !$scheduleDate['day']) {
                $fromTime = new \DateTime($scheduleDate['year'] . '-' . $scheduleDate['month'] . '-01');
                $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next month');
                $orderDate ? $result->andWhere('i.order_date >= :fromTime')
                             ->andWhere('i.order_date < :toTime')
                             ->setParameter('fromTime', $fromTime)
                             ->setParameter('toTime', $toTime)
                           : $result->where('i.order_date >= :fromTime')
                             ->andWhere('i.order_date < :toTime')
                             ->setParameter('fromTime', $fromTime)
                             ->setParameter('toTime', $toTime);
            }
            if ($scheduleDate['year'] and !$scheduleDate['month']) {
                $fromTime = new \DateTime($scheduleDate['year'] . '-01' . '-01');
                $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next year');
                $orderDate ? $result->andWhere('i.order_date >= :fromTime')
                             ->andWhere('i.order_date < :toTime')
                             ->setParameter('fromTime', $fromTime)
                             ->setParameter('toTime', $toTime)
                           : $result->where('i.order_date >= :fromTime')
                             ->andWhere('i.order_date < :toTime')
                             ->setParameter('fromTime', $fromTime)
                             ->setParameter('toTime', $toTime);
            }
        }
        return $result->getQuery()->getResult();
    }
}
