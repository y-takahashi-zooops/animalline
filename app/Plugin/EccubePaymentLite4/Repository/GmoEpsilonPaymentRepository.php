<?php

namespace Plugin\EccubePaymentLite4\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\EccubePaymentLite4\Entity\GmoEpsilonPayment;
use Doctrine\Persistence\ManagerRegistry;

class GmoEpsilonPaymentRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmoEpsilonPayment::class);
    }
}
