<?php

namespace Customize\Repository;

use Customize\Entity\BreederPetImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BreederPetImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method BreederPetImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method BreederPetImage[]    findAll()
 * @method BreederPetImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreederPetImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreederPetImage::class);
    }
}
