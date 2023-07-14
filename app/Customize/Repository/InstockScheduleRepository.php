<?php

namespace Customize\Repository;

use Customize\Entity\InstockSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InstockSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstockSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstockSchedule[]    findAll()
 * @method InstockSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstockScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstockSchedule::class);
    }

    /**
     * Search instock schedule header admin
     * @param null $orderDate
     * @param null $scheduleDate
     * @return array
     * @throws Exception
     */
    public function search($orderDate = null,$orderDate2 = null, $scheduleDate = null,$scheduleDate2 = null):array
    {
        $result = $this->createQueryBuilder('d');
        $result = $result->leftJoin('d.InstockHeader','i');

        if ($orderDate['orderDateYear'] && $orderDate['orderDateMonth'] && $orderDate['orderDateDay'] && $orderDate2['orderDateYear'] && $orderDate2['orderDateMonth'] && $orderDate2['orderDateDay']) {
            $orderDateInput = new \DateTime($orderDate['orderDateYear'] . '-' . $orderDate['orderDateMonth'] . '-' . $orderDate['orderDateDay']);
            $orderDateInput2 = new \DateTime($orderDate2['orderDateYear'] . '-' . $orderDate2['orderDateMonth'] . '-' . $orderDate2['orderDateDay']);

            $result = $result->andWhere('i.order_date between :fromTimeYearOrderDate and :toTimeYearOrderDate');
            $result = $result->setParameter('fromTimeYearOrderDate', $orderDateInput);
            $result = $result->setParameter('toTimeYearOrderDate', $orderDateInput2);
        }

        if ($scheduleDate['scheduleDateYear'] && $scheduleDate['scheduleDateMonth'] && $scheduleDate['scheduleDateDay'] && $scheduleDate2['scheduleDateYear'] && $scheduleDate2['scheduleDateMonth'] && $scheduleDate2['scheduleDateDay']) {
            $scheduleDateInput = new \DateTime($scheduleDate['scheduleDateYear'] . '-' . $scheduleDate['scheduleDateMonth'] . '-' . $scheduleDate['scheduleDateDay']);
            $scheduleDateInput2 = new \DateTime($scheduleDate2['scheduleDateYear'] . '-' . $scheduleDate2['scheduleDateMonth'] . '-' . $scheduleDate2['scheduleDateDay']);

            $result = $result->andWhere('i.arrival_date_schedule between :fromScheduleDateInput and :toScheduleDateInput');
            $result = $result->setParameter('fromScheduleDateInput', $scheduleDateInput);
            $result = $result->setParameter('toScheduleDateInput', $scheduleDateInput2);
        }

        return $result->addOrderBy('i.update_date', 'DESC')->getQuery()->getResult();
    }

    // /**
    //  * @return InstockSchedule[] Returns an array of InstockSchedule objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InstockSchedule
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
