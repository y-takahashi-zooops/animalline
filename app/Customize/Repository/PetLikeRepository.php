<?php

namespace Customize\Repository;

use Customize\Entity\PetLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetLike[]    findAll()
 * @method PetLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PetLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetLike::class);
    }

    public function getLike($sess_id, $site_type, $pet_id){
        $like = $this->findOneBy(["session_id" => $sess_id,"site_type" => $site_type,"pet_id" => $pet_id]);
        
        return $like;
    }

    public function getLikeCount($site_type, $pet_id){
        $likes= $this->findBy(["site_type" => $site_type,"pet_id" => $pet_id]);
        
        return count($likes);
    }
}
