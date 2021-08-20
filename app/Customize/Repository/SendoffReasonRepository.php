<?php

namespace Customize\Repository;

use Customize\Entity\SendoffReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SendoffReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method SendoffReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method SendoffReason[]    findAll()
 * @method SendoffReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SendoffReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SendoffReason::class);
    }
}
