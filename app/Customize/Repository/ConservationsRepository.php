<?php

namespace Customize\Repository;

use Customize\Entity\Conservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Util\StringUtil;
use Eccube\Entity\Master\CustomerStatus;

/**
 * @method Conservations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conservations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conservations[]    findAll()
 * @method Conservations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conservations::class);
    }

    // /**
    //  * @return Conservations[] Returns an array of Conservations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conservations
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function newAdoption()
    {
        $CustomerStatus = $this->getEntityManager()
            ->find(CustomerStatus::class, CustomerStatus::PROVISIONAL);

        $Conservation = new \Customize\Entity\Conservations();
        $Conservation
            ->setStatus($CustomerStatus);

        return $Conservation;
    }

    /**
     * ユニークなシークレットキーを返す.
     *
     * @return string
     */
    public function getUniqueSecretKey()
    {
        do {
            $key = StringUtil::random(32);
            $Conservation = $this->findOneBy(['secret_key' => $key]);
        } while ($Conservation);

        return $key;
    }    
}
