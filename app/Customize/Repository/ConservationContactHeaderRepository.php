<?php

namespace Customize\Repository;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContactHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConservationContactHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConservationContactHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConservationContactHeader[]    findAll()
 * @method ConservationContactHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationContactHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationContactHeader::class);
    }

    /**
     * Check pet isset contact
     * @param $user
     * @param $pet
     * @return bool
     */
    public function checkContacted($user, $pet): bool
    {
        return (bool)$this->createQueryBuilder('ch')
            ->where('ch.Customer = :customer')
            ->andWhere('ch.Pet = :pet')
            ->andWhere('ch.contract_status != :status')
            ->setParameters(['customer' => $user, 'pet' => $pet, 'status' => AnilineConf::CONTRACT_STATUS_NONCONTRACT])
            ->getQuery()
            ->getResult();
    }
}
