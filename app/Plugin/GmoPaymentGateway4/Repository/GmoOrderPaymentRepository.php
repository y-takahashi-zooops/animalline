<?php

/*
 * Copyright(c) 2018 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\GmoPaymentGateway4\Entity\GmoOrderPayment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * GmoOrderPaymentRepository
 */
class GmoOrderPaymentRepository extends AbstractRepository
{
    /**
     * GmoOrderPaymentRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmoOrderPayment::class);
    }
}
