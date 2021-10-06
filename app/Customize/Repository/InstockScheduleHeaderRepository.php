<?php

namespace Customize\Repository;

use Customize\Entity\InstockScheduleHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method InstockScheduleHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstockScheduleHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstockScheduleHeader[]    findAll()
 * @method InstockScheduleHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstockScheduleHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstockScheduleHeader::class);
    }

    /**
     * Search instock schedule header admin
     * @param null $orderDate
     * @param null $scheduleDate
     * @return array|int|mixed|string
     * @throws Exception
     */
    public function search($orderDate = null, $scheduleDate = null)
    {
        $result = $this->createQueryBuilder('i');

        if ($orderDate['orderDateYear']) {
            $fromTimeYearOrderDate = new \DateTime($orderDate['orderDateYear'] . '-01' . '-01');
            $toTimeYearOrderDate = new \DateTime($fromTimeYearOrderDate->format('Y-m-d') . ' first day of next year');
            $result = $result->where('i.order_date >= :fromTimeYearOrderDate')
                ->andWhere('i.order_date < :toTimeYearOrderDate')
                ->setParameter('fromTimeYearOrderDate', $fromTimeYearOrderDate)
                ->setParameter('toTimeYearOrderDate', $toTimeYearOrderDate);
            if ($orderDate['orderDateMonth']) {
                $fromTimeMonthOrderDate = new \DateTime($orderDate['orderDateYear'] . '-' . $orderDate['orderDateMonth'] . '-01');
                $toTimeMonthOrderDate = new \DateTime($fromTimeMonthOrderDate->format('Y-m-d') . ' first day of next month');
                $result = $result->andWhere('i.order_date >= :fromTimeMonthOrderDate')
                    ->andWhere('i.order_date < :toTimeMonthOrderDate')
                    ->setParameter('fromTimeMonthOrderDate', $fromTimeMonthOrderDate)
                    ->setParameter('toTimeMonthOrderDate', $toTimeMonthOrderDate);
                if ($orderDate['orderDateDay']) {
                    $orderDateInput = new \DateTime($orderDate['orderDateYear'] . '-' . $orderDate['orderDateMonth'] . '-' . $orderDate['orderDateDay']);
                    $result = $result->andWhere('i.order_date = :orderDateInput')
                        ->setParameter('orderDateInput', $orderDateInput);
                }
            }
        } else {
            if ($orderDate['orderDateMonth'] or $orderDate['orderDateDay']) {
                return [];
            }
        }

        if ($scheduleDate['scheduleDateYear']) {
            $fromTimeYearScheduleDate = new \DateTime($scheduleDate['scheduleDateYear'] . '-01' . '-01');
            $toTimeYearScheduleDate = new \DateTime($fromTimeYearScheduleDate->format('Y-m-d') . ' first day of next year');
            $result = $orderDate ? $result->andWhere('i.arrival_date_schedule >= :fromTimeYearScheduleDate')
                ->andWhere('i.arrival_date_schedule < :toTimeYearScheduleDate')
                ->setParameter('fromTimeYearScheduleDate', $fromTimeYearScheduleDate)
                ->setParameter('toTimeYearScheduleDate', $toTimeYearScheduleDate)
                : $result->where('i.arrival_date_schedule >= :fromTimeYearScheduleDate')
                    ->andWhere('i.arrival_date_schedule < :toTimeYearScheduleDate')
                    ->setParameter('fromTimeYearScheduleDate', $fromTimeYearScheduleDate)
                    ->setParameter('toTimeYearScheduleDate', $toTimeYearScheduleDate);

            if ($scheduleDate['scheduleDateMonth']) {
                $fromTimeMonthScheduleDate = new \DateTime($scheduleDate['scheduleDateYear'] . '-' . $scheduleDate['scheduleDateMonth'] . '-01');
                $toTimeMonthScheduleDate = new \DateTime($fromTimeMonthScheduleDate->format('Y-m-d') . ' first day of next month');
                $result = $result->andWhere('i.arrival_date_schedule >= :fromTimeMonthScheduleDate')
                    ->andWhere('i.arrival_date_schedule < :toTimeMonthScheduleDate')
                    ->setParameter('fromTimeMonthScheduleDate', $fromTimeMonthScheduleDate)
                    ->setParameter('toTimeMonthScheduleDate', $toTimeMonthScheduleDate);
                if ($scheduleDate['scheduleDateDay']) {
                    $scheduleDateInput = new \DateTime($scheduleDate['scheduleDateYear'] . '-' . $scheduleDate['scheduleDateMonth'] . '-' . $scheduleDate['scheduleDateDay']);
                    $result = $result->andWhere('i.arrival_date_schedule = :scheduleDateInput')
                        ->setParameter('scheduleDateInput', $scheduleDateInput);
                }
            }
        } else {
            if ($scheduleDate['scheduleDateMonth'] or $scheduleDate['scheduleDateDay']) {
                return [];
            }
        }

        return $result->addOrderBy('i.update_date', 'DESC')->getQuery()->getResult();
    }
}
