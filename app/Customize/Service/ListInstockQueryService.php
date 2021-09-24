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

    public function search($orderDate = null,  $scheduleDate = null)
    {
        $result = $this->instockScheduleHeaderRepository->createQueryBuilder('i');

        if ($orderDate['orderDateYear'] and $orderDate['orderDateMonth'] and $orderDate['orderDateDay']) {
            $orderDateInput = new \DateTime($orderDate['orderDateYear'] . '-' . $orderDate['orderDateMonth'] . '-' . $orderDate['orderDateDay']);
            $result = $result->where('i.order_date = :orderDateInput')
                ->setParameter('orderDateInput', $orderDateInput);
        }
        if ($scheduleDate['scheduleDateYear'] and $scheduleDate['scheduleDateMonth'] and $scheduleDate['scheduleDateDay']) {
            $scheduleDateInput = new \DateTime($scheduleDate['scheduleDateYear'] . '-' . $scheduleDate['scheduleDateMonth'] . '-' . $scheduleDate['scheduleDateDay']);
            $orderDate ? $result = $result->andWhere('i.arrival_date_schedule = :scheduleDateInput')
                         ->setParameter('scheduleDateInput', $scheduleDateInput)
                        : $result = $result->where('i.arrival_date_schedule = :scheduleDateInput')
                        ->setParameter('scheduleDateInput', $scheduleDateInput);
        }


        if ($orderDate['orderDateYear'] and $orderDate['orderDateMonth'] and !$orderDate['orderDateDay']) {
            $fromTime = new \DateTime($orderDate['orderDateYear'] . '-' . $orderDate['orderDateMonth'] . '-01');
            $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next month');
            $result = $result->where('i.order_date >= :fromTime')
                ->andWhere('i.order_date < :toTime')
                ->setParameter('fromTime', $fromTime)
                ->setParameter('toTime', $toTime);
        }
        if ($scheduleDate['scheduleDateYear'] and $scheduleDate['scheduleDateMonth'] and !$scheduleDate['scheduleDateDay']) {
            $fromTime = new \DateTime($scheduleDate['scheduleDateYear'] . '-' . $scheduleDate['scheduleDateMonth'] . '-01');
            $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next month');
            $orderDate ? $result->andWhere('i.arrival_date_schedule >= :fromTime')
                         ->andWhere('i.arrival_date_schedule < :toTime')
                         ->setParameter('fromTime', $fromTime)
                         ->setParameter('toTime', $toTime)
                       : $result->where('i.arrival_date_schedule >= :fromTime')
                         ->andWhere('i.arrival_date_schedule < :toTime')
                         ->setParameter('fromTime', $fromTime)
                         ->setParameter('toTime', $toTime);
        }


        if ($orderDate['orderDateYear'] and !$orderDate['orderDateMonth']) {
            $fromTime = new \DateTime($orderDate['orderDateYear'] . '-01' . '-01');
            $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next year');
            $result = $result->where('i.order_date >= :fromTime')
                ->andWhere('i.order_date < :toTime')
                ->setParameter('fromTime', $fromTime)
                ->setParameter('toTime', $toTime);
        }
        if ($scheduleDate['scheduleDateYear'] and !$scheduleDate['scheduleDateMonth']) {
            $fromTime = new \DateTime($scheduleDate['scheduleDateYear'] . '-01' . '-01');
            $toTime = new \DateTime($fromTime->format('Y-m-d') . ' first day of next year');
            $orderDate ? $result->andWhere('i.arrival_date_schedule >= :fromTime')
                         ->andWhere('i.arrival_date_schedule < :toTime')
                         ->setParameter('fromTime', $fromTime)
                         ->setParameter('toTime', $toTime)
                       : $result->where('i.arrival_date_schedule >= :fromTime')
                         ->andWhere('i.arrival_date_schedule < :toTime')
                         ->setParameter('fromTime', $fromTime)
                         ->setParameter('toTime', $toTime);
        }
        return $result->addOrderBy('i.update_date', 'DESC')->getQuery()->getResult();
    }
}
