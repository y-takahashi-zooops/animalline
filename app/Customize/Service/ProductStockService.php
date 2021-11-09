<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;

use Customize\Repository\ProductSetRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductStockRepository;
use Eccube\Entity\ProductClass;

class ProductStockService extends AbstractController
{

    public function __construct(
        ProductSetRepository $productSetRepository,
        ProductClassRepository $productClassRepository,
        ProductStockRepository $productStockRepository
    ) {
        $this->productSetRepository = $productSetRepository;
        $this->productClassRepository = $productClassRepository;
        $this->productStockRepository = $productStockRepository;
    }

    public function setStock(EntityManagerInterface $em, ProductClass $pc, int $stock) {
        $diff = $stock - $pc->getStock();

        $this->calculateStock($em, $pc, $diff);
    }

    public function calculateStock(EntityManagerInterface $em, ProductClass $pc, int $stockadd) {
        //ProductClassの在庫増減
        $this->setProductStock($em, $pc, $pc->getStock() + $stockadd);

        //セット品の親の場合子商品も変更
        $childs = $this->productSetRepository->findBy(['ParentProductClass' => $pc]);
        foreach ($childs as $child) {
            $child_pc = $child->getProductClass();
            $this->setProductStock($em, $child_pc, $child_pc->getStock() + ($stockadd * $child->getSetUnit()));
        }

        //セット品の子の場合、セット商品の一番在庫の小さい値を親にセット
        $set_pc = $this->productSetRepository->findOneBy(['ProductClass' => $pc]);
        if($set_pc){
            $qb = $this->productSetRepository->createQueryBuilder('s')
                ->select('min(c.stock) as mstock')
                ->leftJoin('s.ProductClass', 'c')
                ->where('s.ParentProductClass = :pc')
                ->groupBy('s.ParentProductClass')
                ->setParameter('pc', $set_pc->getParentProductClass())
                ->setMaxResults(1);

            $records = $qb->getQuery()->getArrayResult();

            if($records){
                $parent_stock = intval($records[0]["mstock"]);

                $this->setProductStock($em, $set_pc->getParentProductClass(), $parent_stock);
            }
        }
    }

    // ProductClassの在庫数をProductStockにコピーする
    public function setProductStock(EntityManagerInterface $em, ProductClass $pc, $stock) {
        // 在庫更新
        $pc->setStock($stock);
        $em->persist($pc);

        // 在庫レコード取得
        $ps = $this->productStockRepository->findOneBy(['ProductClass' => $pc]);
        $ps->setStock($pc->getStock());
        $em->persist($ps);

        $em->flush();
    }
    
}
