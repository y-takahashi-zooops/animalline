<?php

namespace Customize\Repository;

use Customize\Config\AnilineConf;
use Customize\Entity\BreederContactHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederContactHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederContactHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederContactHeader[]    findAll()
 * @method BreederContactHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederContactHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederContactHeader::class);
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

    /**
     * Get last contract header by pet
     * 
     * @param  mixed  $pet
     * @return array
     */
    public function findLastContractHeaderByPet($pet): array
    {
        return $this->createQueryBuilder('ch')
            ->where('ch.Pet = :pet')
            ->setParameter('pet', $pet)
            ->addOrderBy('ch.last_message_date', 'DESC')
            ->addOrderBy('ch.id', 'DESC')
            ->select('ch.contract_status')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get contact header a month
     *
     * @param $startDate
     * @param $endDate
     * @param $breeder
     * @return array
     */
    public function getContractHeaderAMonth($startDate, $endDate, $breeder): array
    {
        $qb = $this->createQueryBuilder('ch');

        $qb->where('ch.update_date >= :startDate')
            ->andWhere('ch.update_date <= :endDate')
            ->andWhere('ch.contract_status = ' . AnilineConf::CONTRACT_STATUS_CONTRACT)
            ->andWhere('ch.Breeder = :breeder')
            ->setParameters(['startDate' => $startDate, 'endDate' => $endDate, 'breeder' => $breeder]);

        return $qb->getQuery()
            ->getResult();
    }
}
