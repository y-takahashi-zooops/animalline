<?php

namespace Customize\Repository;

use Customize\Entity\ConservationBankAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConservationBankAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConservationBankAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConservationBankAccount[]    findAll()
 * @method ConservationBankAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationBankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationBankAccount::class);
    }
}
