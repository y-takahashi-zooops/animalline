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

namespace Customize\Controller\Admin\Product;

use Customize\Entity\ProductSet;
use Customize\Form\Type\Admin\ProductSetType;
use Customize\Repository\ProductSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Controller\AbstractController;
use Eccube\Entity\OrderItem;
use Eccube\Form\Type\Admin\SearchProductType;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProductSetController extends AbstractController
{
    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductSetRepository
     */
    protected $productSetRepository;

    /**
     * @var OrderItemTypeRepository
     */
    protected $orderItemTypeRepository;

    /**
     * ProductController constructor.
     *
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param ProductSetRepository $productSetRepository
     * @param OrderItemTypeRepository $orderItemTypeRepository
     */
    public function __construct(
        ProductClassRepository  $productClassRepository,
        ProductRepository       $productRepository,
        ProductSetRepository    $productSetRepository,
        OrderItemTypeRepository $orderItemTypeRepository
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->productSetRepository = $productSetRepository;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
    }

    /**
     * セット商品管理画面
     *
     * @Route("/%eccube_admin_route%/product/set/{id}", name="admin_product_set")
     * @Template("@admin/Product/product_set.twig")
     */
    public function product_set(Request $request, $id)
    {
        $Product = $this->productRepository->find($id);
        $ProductClass = $this->productClassRepository->findOneBy(['Product' => $Product]);
        if (!$Product || !$ProductClass) {
            throw new NotFoundHttpException();
        }
        $countSet = 0;
        $OriginSets = new ArrayCollection();
        foreach ($this->productSetRepository->findBy(['ParentProduct' => $Product]) as $set) {
            $countSet++;
            $item = new OrderItem;
            $item->setId($set->getId());
            $item->setOrderItemType($this->orderItemTypeRepository->find(1));
            $item->setQuantity($set->getSetUnit());
            $item->setProduct($set->getProduct());
            $item->setPrice($set->getProductClassId()->getPrice02());
            $item->setProductClass($set->getProductClassId());
            $item->setProductName($set->getProduct()->getName());
            $item->setProductCode($set->getProductClassId()->getCode());
            if ($set->getProductClassId()->getClassCategory1()) {
                $item->setClassName1('フレーバー');
                $item->setClassCategoryName1($set->getProductClassId()->getClassCategory1()->getName());
            }
            if ($set->getProductClassId()->getClassCategory2()) {
                $item->setClassName2('サイズ');
                $item->setClassCategoryName2($set->getProductClassId()->getClassCategory2()->getName());
            }
            $OriginSets->add($item);
        }
        $Product->setProductSet();
        foreach ($OriginSets as $key => $item) {
            $Product->addProductSet($item);
        }
        $builder = $this->formFactory->createBuilder(ProductSetType::class, $Product);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form['ProductSet']->isValid()) {
            $items = $form['ProductSet']->getData();
            switch ($request->get('mode')) {
                case 'register':
                    if ($form->isValid()) {
                        $Product->setProductSet(); // clear temp orderitem data
                        $idDb = [];
                        $idReq = [];
                        foreach ($this->productSetRepository->findBy(['ParentProduct' => $Product]) as $item) {
                            array_push($idDb, $item->getId());
                        }
                        foreach ($items as $key => $item) {
                            array_push($idReq, $item['id']);
                            if ($item['id']) {
                                $ProductSet = $this->productSetRepository->find($item['id']);
                                $ProductSet->setSetUnit($item->getQuantity());
                            } else {
                                $ProductSet = (new ProductSet())
                                    ->setSetUnit($item->getQuantity())
                                    ->setParentProduct($Product)
                                    ->setParentProductClass($ProductClass)
                                    ->setProduct($item->getProduct())
                                    ->setProductClassId($item->getProductClass());
                            }
                            $this->entityManager->persist($ProductSet);
                        }
                        foreach ($idDb as $item) {
                            if (!in_array($item, $idReq)) {
                                $objDel = $this->productSetRepository->find($item);
                                $this->entityManager->remove($objDel);
                            }
                        }
                        $this->entityManager->flush();

                        $this->addSuccess('admin.common.save_complete', 'admin');
                        return $this->redirectToRoute('admin_product');
                    }
                    break;
                default:
                    break;
            }
        }
        $builder = $this->formFactory->createBuilder(SearchProductType::class);
        $searchProductModalForm = $builder->getForm();
        return [
            'form' => $form->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Product' => $Product,
            'ProductClass' => $ProductClass,
            'count' => $countSet
        ];
    }
}
