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

use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Admin\SearchProductType;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
     * ProductController constructor.
     *
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductClassRepository          $productClassRepository,
        ProductRepository               $productRepository
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
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

        // if ($form->isSubmitted() && $form->isValid()) {
        // }

        // 商品検索フォーム
        $searchProductModalForm = $this->createForm(SearchProductType::class);

        return [
            'Product' => $Product,
            'ProductClass' => $ProductClass,
            'searchProductModalForm' => $searchProductModalForm->createView()
        ];
    }
}
