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
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class ProductMakerController extends AbstractController
{
    /**
     * @var ProductMakerRepository
     */
    protected $productMakerRepository;

    /**
     * Product maker controller constructor.
     *
     * @param ProductMakerRepository $productMakerRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProductMakerRepository $productMakerRepository,
        FormFactoryInterface $formFactory
    ) {
        $this->productMakerRepository = $productMakerRepository;
        
        // 親クラスのsetterメソッドを呼び出してプロパティを設定
        $this->setEntityManager($entityManager);
        $this->setFormFactory($formFactory);
    }

    /**
     * メーカーマスタ管理実装
     *
     * @Route("/%eccube_admin_route%/product/maker", name="admin_product_maker")
     * @Template("@admin/Product/maker.twig")
     */
    public function productMaker(Request $request, PaginatorInterface $paginator)
    {
        // delete product maker
        $idDestroy = $request->get('id-destroy');
        if (
            $idDestroy &&
            $Maker = $this->productMakerRepository->find($idDestroy)
        ) {
            $entityManager = $this->entityManager;
            $entityManager->remove($Maker);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_maker');
        }

        // create product maker
        $Maker = new ProductMaker();
        $form = $this->createForm(ProductMakerType::class, $Maker);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($Maker);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_maker');
        }

        // update product maker
        $Makers = $this->productMakerRepository->findBy(
            [],
            ['update_date' => 'DESC', 'id' => 'DESC']
        );
        $formUpdate = [];
        foreach ($Makers as $Maker) {
            $uniqueFormName = 'Form' . $Maker->getId();
            // $formHandle = $this->get('form.factory')->createNamed($uniqueFormName, ProductMakerType::class, $Maker);
            $formHandle = $this->formFactory->createNamed($uniqueFormName, ProductMakerType::class, $Maker);
            $formUpdate[$uniqueFormName] = $formHandle;
        }
        $formUpdateView = [];
        foreach ($formUpdate as $formName => $formHandle) {
            $makerId = $request->get('product-maker-id');
            if (
                $makerId &&
                $Maker = $this->productMakerRepository->find($makerId)
            ) {
                $formHandle->handleRequest($request);
                if ($formHandle->isSubmitted() && $formHandle->isValid()) {
                    $entityManager = $this->entityManager;
                    $entityManager->persist($Maker);
                    $entityManager->flush();
                }
            }
            $formUpdateView[$formName] = $formHandle->createView();
        }

        $results = $paginator->paginate(
            $Makers,
            $request->query->getInt('page') ?: 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return [
            'makers' => $results,
            'form' => $form->createView(),
            'form_update' => $formUpdateView
        ];
    }
}
