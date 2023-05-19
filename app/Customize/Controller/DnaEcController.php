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
use Customize\Entity\DnaSalesHeader;
use Customize\Entity\DnaSalesStatus;
use Customize\Form\Type\DnaSalesType;
use Customize\Repository\DnaSalesHeaderRepository;
use Customize\Repository\DnaSalesDetailRepository;
use Customize\Repository\DnaSalesStatusRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Eccube\Controller\ProductController as BaseProductController;
use Eccube\Service\CartService;
use Eccube\Form\Type\AddCartType;
use Eccube\Repository\CartItemRepository;
use Eccube\Repository\CartRepository;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

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
     * @var DnaSalesStatusRepository
     */
    protected $dnaSalesStatusRepository;

    /**
     * @var DnaSalesHeaderRepository
     */
    protected $dnaSalesHeaderRepository;

    /**
     * @var DnaSalesDetailRepository
     */
    protected $dnaSalesDetailRepository;

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
        DnaSalesStatusRepository $dnaSalesStatusRepository,
        DnaSalesHeaderRepository $dnaSalesHeaderRepository,
        DnaSalesDetailRepository $dnaSalesDetailRepository
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
        $this->dnaSalesStatusRepository = $dnaSalesStatusRepository;
        $this->dnaSalesHeaderRepository = $dnaSalesHeaderRepository;
        $this->dnaSalesDetailRepository = $dnaSalesDetailRepository;
    }


    /**
     * @Route("/ec/dna", name="dna_ec_top")
     * @Template("dna_ec.twig")
     */
    public function dna_ec(Request $request)
    {
        $customer = $this->getUser();

        if(!$customer){
            $this->setLoginTargetPath('dna_ec_top');
            return $this->redirectToRoute("mypage_login");
        }

        //$contact_pet_id = $request->cookies->get('contact_pet');
        //$response = new Response();
        //$response->headers->setCookie(new Cookie('rid_key', $sessid));

        //未購入のDNA検査情報があるか
        $dnaSalesHeader = $this->dnaSalesHeaderRepository->findOneBy(['Customer' => $customer, 'shipping_status' => 0]);
        $dnaSalesStatus = $this->dnaSalesStatusRepository->findBy(['DnaSalesHeader' => $dnaSalesHeader]);

        $salesDetail = $this->dnaSalesStatusRepository->createQueryBuilder('ds')
            ->innerJoin('Customize\Entity\DnaSalesHeader', 'dh', 'WITH', 'dh.id = ds.DnaSalesHeader')
            ->where('dh.Customer = :customer_id')
            ->select('')
            ->setParameter('customer_id', $this->getUser()->getId())
            ->getQuery()->getResult();

        
        return $this->render('dna_ec.twig', [
            'header' => $dnaSalesHeader,
            'details' => $dnaSalesStatus
        ]);
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
            $entityManager = $this->getDoctrine()->getManager();

            //未購入のDNA検査情報があるか
            $dnaSalesHeader = $this->dnaSalesHeaderRepository->findOneBy(['Customer' => $customer, 'shipping_status' => 0]);

            if(!$dnaSalesHeader){
                $dnaSalesHeader = new DnaSalesHeader();
                $dnaSalesHeader->setCustomer($customer);
                $dnaSalesHeader->setShippingStatus(0);
            }
            $total_price = $dnaSalesHeader->getTotalPrice() + $request->get('total_price');

            $dnaSalesHeader->setTotalPrice($total_price);
            $dnaSalesHeader->setShippingCity('');
            $entityManager->persist($dnaSalesHeader);
            $entityManager->flush();

            $dnaSalesStatus = new DnaSalesStatus();
            $dnaSalesStatus->setDnaSalesHeader($dnaSalesHeader);
            $dnaSalesStatus->setPetKind((int)$request->get('pet_type'));
            $dnaSalesStatus->setPrice((int)$request->get('total_price'));
            $dnaSalesStatus->setBreedsType($this->breedsRepository->find($request->get('pet_kind')));
            $dnaSalesStatus->setCheckStatus(0);
            $dnaSalesStatus->setTestCount(0);
            $entityManager->persist($dnaSalesStatus);

            $test_cnt = 0;
            foreach ($request->get('status_detail') as $value) {
                $dnaSalesDetail = new DnaSalesDetail();
                $dnaSalesDetail->setDnaSalesStatus($dnaSalesStatus);
                $dnaSalesDetail->setAlmDnaCheckKindsId($value);
                $dnaSalesDetail->setCheckResult(1);
                $entityManager->persist($dnaSalesDetail);
                $entityManager->flush();

                $test_cnt++;
            }

            //検査項目数格納
            $dnaSalesStatus->setTestCount($test_cnt);

            $entityManager->persist($dnaSalesStatus);
            $entityManager->persist($dnaSalesDetail);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('dna_ec_top'));
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
        $customer = $this->getUser();

        if(!$customer){
            $this->setLoginTargetPath('dna_ec_top');
            return $this->redirectToRoute("mypage_login");
        }

        $dnaSalesHeader = $this->dnaSalesHeaderRepository->findOneBy(['Customer' => $customer, 'shipping_status' => 0]);
        $dnaSalesStatus = $this->dnaSalesStatusRepository->findBy(['DnaSalesHeader' => $dnaSalesHeader]);

        $this->cartService->clear();

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

        $form = $builder->getForm();
        $form->handleRequest($request);
        
        $addCartData = $form->getData();

        foreach($dnaSalesStatus as $details) {
            /*
            $param['quantity'] = 1;
            $param['normal_quantity'] = 1;
            $param['product_id'] = 3065;
            $param['ProductClass'] = 2438;
            $param['is_repeat'] = "";
            $param['repeat_span'] = "";
            $param['span_unit'] = "";
            $param['_token'] = $request->get("_token");

            $url = $this->generateUrl('product_add_cart', ['id' => 3065]);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $http_str = curl_exec($ch);
            var_dump($http_str);
            curl_close($ch);

            return $this->render('dna_ec.twig', [
                'header' => null,
                'details' => null
            ]);
            */
            
            $this->cartService->addProduct(
                $addCartData['product_class_id'],
                $addCartData['quantity'],
                $addCartData['is_repeat'],
                $addCartData['repeat_span'],
                $addCartData['span_unit']
            );

            $addCartData['product_class_id']  = 2446;
        }

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
