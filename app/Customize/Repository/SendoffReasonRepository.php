<?php

namespace Customize\Repository;

use Customize\Entity\SendoffReasons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SendoffReasons|null find($id, $lockMode = null, $lockVersion = null)
 * @method SendoffReasons|null findOneBy(array $criteria, array $orderBy = null)
 * @method SendoffReasons[]    findAll()
 * @method SendoffReasons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SendoffReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SendoffReasons::class);
    }
}
