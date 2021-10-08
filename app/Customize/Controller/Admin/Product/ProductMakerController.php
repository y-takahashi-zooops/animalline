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

use Customize\Config\AnilineConf;
use Customize\Entity\ProductMaker;
use Customize\Form\Type\Admin\ProductMakerType;
use Customize\Repository\ProductMakerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Controller\Admin\Product\ProductController as BaseProductController;

class ProductMakerController extends BaseProductController
{
    /**
     * @var ProductMakerRepository
     */
    protected $productMakerRepository;

    /**
     * ProductController constructor.
     *
     * @param ProductMakerRepository $productMakerRepository
     */
    public function __construct(
        ProductMakerRepository $productMakerRepository
    ) {
        $this->productMakerRepository = $productMakerRepository;
    }

    /**
     * 仕入先管理
     *
     * @Route("/%eccube_admin_route%/product/maker", name="admin_product_maker")
     * @Template("@admin/Product/maker.twig")
     */
    public function supplier(Request $request, PaginatorInterface $paginator)
    {

        $makerNew = new ProductMaker();

        $form = $this->createForm(ProductMakerType::class, $makerNew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($makerNew);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_maker');
        }

        $makers = $this->productMakerRepository->findAll();

        $results = $paginator->paginate(
            $makers,
            $request->query->getInt('page') ?: 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return [
            'makers' => $results,
            'form' => $form->createView()
        ];
    }
}
