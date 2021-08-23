<?php

namespace Customize\Repository;

use Customize\Entity\BreederContacts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederContacts|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederContacts|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederContacts[]    findAll()
 * @method BreederContacts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederContactsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederContacts::class);
    }
}
