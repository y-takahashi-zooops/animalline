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

namespace Customize\Controller;

use Customize\Form\Type\TrainingType;
use Customize\Service\MailService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\NewsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\CategoryRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckKindsEcRepository;
use Customize\Entity\DnaCheckKindsEc;
use Customize\Entity\DnaSalesDetail;
use Customize\Form\Type\DnaSalesType;
use Customize\Repository\DnaSalesHeaderRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Eccube\Controller\ProductController as BaseProductController;
use Eccube\Service\CartService;
use Eccube\Form\Type\AddCartType;
use Eccube\Repository\CartItemRepository;
use Eccube\Repository\CartRepository;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseContext;

class DnaEcController extends BaseProductController
{
    /**
     * @var NewsRepository
     */
    protected $NewsRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;
    
    /**
     * @var ProductListOrderByRepository
     */
    protected $productListOrderByRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var DnaCheckKindsEcRepository
     */
    protected $dnaCheckKindsEcRepository;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var CartItemRepository
     */
    protected $cartItemRepository;

    /**
     * @var CartRepository
     */
    protected $cartRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var DnaSalesHeaderRepository
     */
    protected $dnaSalesHeaderRepository;

    public function __construct(
        NewsRepository $NewsRepository,
        ProductRepository $productRepository,
        ProductClassRepository $productClassRepository,
        CategoryRepository $categoryRepository,
        ProductListOrderByRepository $productListOrderByRepository,
        MailService $mailService,
        BreedsRepository $breedsRepository,
        DnaCheckKindsEcRepository $dnaCheckKindsEcRepository,
        CartService $cartService,
        CartItemRepository $cartItemRepository,
        CartRepository $cartRepository,
        PurchaseFlow $cartPurchaseFlow,
        DnaSalesHeaderRepository $dnaSalesHeaderRepository
    ) {
        $this->NewsRepository = $NewsRepository;
        $this->productListOrderByRepository = $productListOrderByRepository;
        $this->productRepository = $productRepository;
        $this->productClassRepository = $productClassRepository;
        $this->categoryRepository = $categoryRepository;
        $this->mailService = $mailService;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckKindsEcRepository = $dnaCheckKindsEcRepository;
        $this->cartService = $cartService;
        $this->cartItemRepository = $cartItemRepository;
        $this->cartRepository = $cartRepository;
        $this->purchaseFlow = $cartPurchaseFlow;
        $this->dnaSalesHeaderRepository = $dnaSalesHeaderRepository;
    }


    /**
     * @Route("/ec/dna", name="dna_ec_top")
     * @Template("dna_ec.twig")
     */
    public function dna_ec(Request $request)
    {
        // $customer = $this->getUser();

        // if(!$customer){
        //     $this->setLoginTargetPath('dna_ec_top');
        //     return $this->redirectToRoute("mypage_login");
        // }

        // $breeds = $this->breedsRepository->findAll();
        // $form = $this->createForm(DnaSalesType::class);
        // $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
        //     // var_dump(111111111111111111111); die;
        // }

        // return $this->render('dna_ec.twig', [
        //     'form' => $form->createView(),
        //     'breeds' => $breeds,
        // ]);

        $salesHeader = $this->dnaSalesHeaderRepository->createQueryBuilder('d')
            ->where('d.customer_id = :customer_id',)
            ->setParameter('customer_id', $this->getUser()->getId())
            ->getQuery()->getResult();
        
        dump($salesHeader); die;

    }

    /**
     * @Route("/ec/dna_detail", name="dna_ec_detail")
     * @Template("dna_ec_detail.twig")
     */
    public function dna_detail(Request $request)
    {
        $customer = $this->getUser();

        if(!$customer){
            $this->setLoginTargetPath('dna_ec_top');
            return $this->redirectToRoute("mypage_login");
        }

        $breeds = $this->breedsRepository->findAll();
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            // $entityManager = $this->getDoctrine()->getManager();

            // $dnaSalesDetail = new DnaSalesDetail();
            // $i = 1;
            // while (!empty($request->get('scales-' . $i))) {
            //     $dnaSalesDetail->setCheckStatusId(1);
            //     $dnaSalesDetail->setAlmDnaCheckKindsId(intval($request->get('scales-' . $i)));
            //     $dnaSalesDetail->setCheckResult($request->get('checkResult'));
            //     $i ++;
            // }

            // $entityManager->persist($dnaSalesDetail);
            // $entityManager->flush();
        }

        return $this->render('dna_ec_detail.twig', [
            'form' => $form->createView(),
            'breeds' => $breeds,
        ]);
    }

    /**
     * @Route("/ec/dna_buy", name="dna_buy")
     */
    public function dna_buy(Request $request)
    {
        //検査数チェック
        $cnt = 0;
        for($i=1;$i<=6;$i++){
            if($request->get("check_kind_".$i) == "1"){
                $cnt++;
            }
        }

        $Product = $this->productRepository->find($request->get("product_id"));
        $Product_class = $this->productClassRepository->find($request->get("ProductClass"));

        $builder = $this->formFactory->createNamedBuilder(
            '',
            AddCartType::class,
            null,
            [
                'product' => $Product,
                'id_add_product_id' => false,
            ]
        );

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_CART_ADD_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        //入力チェック
        /*
        if (!$form->isValid()) {
            var_dump($form->getErrors());
            throw new NotFoundHttpException();
        }
        */
        
        $addCartData = $form->getData();
        
        $this->cartService->clear();
        $this->cartService->addProduct(
            $addCartData['product_class_id'],
            $addCartData['quantity'],
            $addCartData['is_repeat'],
            $addCartData['repeat_span'],
            $addCartData['span_unit']
        );

        // 明細の正規化
        $Carts = $this->cartService->getCarts();
        foreach ($Carts as $Cart) {
            $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
            // 復旧不可のエラーが発生した場合は追加した明細を削除.
            if ($result->hasError()) {
                $this->cartService->removeProduct($addCartData['product_class_id']);
                foreach ($result->getErrors() as $error) {
                    $errorMessages[] = $error->getMessage();
                }
            }
            foreach ($result->getWarning() as $warning) {
                $errorMessages[] = $warning->getMessage();
            }
        }

        $this->cartService->save();

        return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * @Route("/ec/dna/get_pet_type/{id}", name="dna_ec_getpet")
     */
    public function dna_ec_getpet($id)
    {
        //$breeds = $this->breedsRepository->findBy(["pet_kind" => $id],["sort_order" => "ASC"]);
        $breeds = $this->breedsRepository->createQueryBuilder('b')
            ->where('EXISTS (SELECT d.id FROM Customize\Entity\DnaCheckKindsEc d WHERE d.Breeds = b)')
            ->andWhere('b.pet_kind = :pet_kind')
            ->setParameter('pet_kind', $id)
            ->orderBy('b.sort_order', 'asc')
            ->getQuery()->getResult();

        //var_dump($breeds);

        $responce = [];
        foreach($breeds as $breed){
            $responce[] = ["id" => $breed->getId(),"breeds_name" => $breed->getBreedsName()];
        }
        //return [];
        return new JsonResponse($responce);
    }

    /**
     * @Route("/ec/dna/get_dna_kinds/{id}", name="dna_ec_detkind")
     */
    public function get_dna_kinds($id)
    {
        $breed = $this->breedsRepository->find($id);

        $check_kinds = $this->dnaCheckKindsEcRepository->findBy(["Breeds" => $breed],["id" => "ASC"]);

        $responce = [];
        foreach($check_kinds as $check_kind){
            $responce[] = ["id" => $check_kind->getId(),"check_kind" => $check_kind->getCheckKind()];
        }
        return new JsonResponse($responce);
    }
}
