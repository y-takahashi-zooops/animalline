<?php

namespace Plugin\EccubePaymentLite4\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\EccubePaymentLite4\Entity\MyPageRegularSetting;
use Doctrine\Persistence\ManagerRegistry;

class MyPageRegularSettingRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyPageRegularSetting::class);
    }
}
