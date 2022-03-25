<?php

namespace Customize\Repository;

use Customize\Entity\MonthlyInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MonthlyInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlyInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlyInvoice[]    findAll()
 * @method MonthlyInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthlyInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyInvoice::class);
    }
}
