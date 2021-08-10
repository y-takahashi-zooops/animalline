<?php

namespace Customize\Repository;

use Customize\Entity\ConservationPetImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConservationPetImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConservationPetImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConservationPetImage[]    findAll()
 * @method ConservationPetImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationPetImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConservationPetImage::class);
    }
}
