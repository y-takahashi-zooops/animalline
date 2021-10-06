<?php

namespace Customize\Repository;

use Customize\Entity\ShippingScheduleHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingScheduleHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingScheduleHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingScheduleHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingScheduleHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingScheduleHeader::class);
    }

    public function findAll(): array
    {
        return $this->findBy(array(), array('update_date' => 'DESC', 'id' => 'DESC'));
    }
}
