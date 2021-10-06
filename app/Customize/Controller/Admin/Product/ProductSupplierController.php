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
use Customize\Entity\Supplier;
use Customize\Form\Type\Admin\SupplierType;
use Customize\Repository\SupplierRepository;
use Eccube\Repository\ProductClassRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Controller\Admin\Product\ProductController as BaseProductController;

class ProductSupplierController extends BaseProductController
{
    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * ProductController constructor.
     *
     * @param ProductClassRepository $productClassRepository
     * @param SupplierRepository $supplierRepository
     */
    public function __construct(
        ProductClassRepository $productClassRepository,
        SupplierRepository     $supplierRepository
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * 仕入先管理
     *
     * @Route("/%eccube_admin_route%/product/supplier", name="admin_product_supplier")
     * @Template("@admin/Product/supplier.twig")
     */
    public function supplier(Request $request, PaginatorInterface $paginator)
    {
        $idDestroy = $request->get('id-destroy');
        if ($idDestroy) {
            $supplier = $this->supplierRepository->find($request->get('id-destroy'));
            $issetProduct = $this->productClassRepository->findBy(['supplier_code' => $supplier->getSupplierCode()]);
            if (!$issetProduct) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($supplier);
                $entityManager->flush();
            }
            return $this->redirectToRoute('admin_product_supplier');
        }
        $supplierNew = new Supplier();

        $form = $this->createForm(SupplierType::class, $supplierNew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supplierNew);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_supplier');
        }

        $suppliers = $this->supplierRepository->findAll();
        $formUpdate = [];
        foreach ($suppliers as $supplier) {
            $uniqueFormName = 'Form' . $supplier->getId();
            $formHandle = $this->get('form.factory')->createNamed($uniqueFormName, SupplierType::class, $supplier);
            $formUpdate[$uniqueFormName] = $formHandle;
            $supplier->is_destroy = (bool)$this->productClassRepository->findBy(['supplier_code' => $supplier->getSupplierCode()]);
        }
        $formUpdateView = [];
        foreach ($formUpdate as $formName => $formHandle) {
            if ($request->get('supplier-id')) {
                $supplier = $this->supplierRepository->find($request->get('supplier-id'));
                $formHandle->handleRequest($request);
                if ($formHandle->isSubmitted() && $formHandle->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($supplier);
                    $entityManager->flush();
                }
            }
            $formUpdateView[$formName] = $formHandle->createView();
        }

        $results = $paginator->paginate(
            $suppliers,
            $request->query->getInt('page') ?: 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return [
            'suppliers' => $results,
            'form' => $form->createView(),
            'form_update' => $formUpdateView
        ];
    }
}
