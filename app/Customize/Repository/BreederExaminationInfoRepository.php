<?php

namespace Customize\Repository;

use Customize\Entity\BreederExaminationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederExaminationInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederExaminationInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederExaminationInfo[]    findAll()
 * @method BreederExaminationInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederExaminationInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederExaminationInfo::class);
    }
}
