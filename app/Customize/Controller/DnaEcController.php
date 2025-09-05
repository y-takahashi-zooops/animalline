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

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
use Doctrine\ORM\EntityManagerInterface;

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
        DnaSalesDetailRepository $dnaSalesDetailRepository,
        EntityManagerInterface $entityManager,
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
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/dna_ec", name="dna_ec_top")
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
     * @Route("/dna_detail", name="dna_ec_detail")
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
            $entityManager = $this->entityManager;

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
                $check_kind = $this->dnaCheckKindsEcRepository->find($value);

                $dnaSalesDetail = new DnaSalesDetail();
                $dnaSalesDetail->setDnaSalesStatus($dnaSalesStatus);
                //$dnaSalesDetail->setAlmDnaCheckKindsId($value);
                $dnaSalesDetail->setDnaCheckKind($check_kind);
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
     * @Route("/dna_buy", name="dna_buy")
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
        
        //カートに入れる商品をセット
        $addCartData['product_class_id']  = 2439;
        foreach($dnaSalesStatus as $details) {
            //商品コードで検索
            $product_code = "DNA00000".$details->getTestCount();
            $product_class = $this->productClassRepository->findOneBy(["code"=> $product_code]);

            if($product_class){
                if(!isset($products[$details->getTestCount()])){
                    //要素作成
                    $products[$details->getTestCount()] = [
                        "product_class_id" => $product_class->getId(),
                        "quantity" => 1
                    ];
                }
                else{
                    //要素作成
                    $products[$details->getTestCount()]["quantity"] = $products[$details->getTestCount()]["quantity"] + 1;
                }
            }
        }

        $this->cartService->addProductMulti($products);

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
     * @Route("/dna_delete", name="dna_delete")
     */
    public function dna_delete(Request $request)
    {
        $customer = $this->getUser();

        if(!$customer){
            $this->setLoginTargetPath('dna_ec_top');
            return $this->redirectToRoute("mypage_login");
        }

        $em = $this->getDoctrine()->getManager();

        $status_id = $request->get("id");
        //明細レコード取得
        $dna_status = $this->dnaSalesStatusRepository->find($status_id);

        if($dna_status){
            $dna_header = $dna_status->getDnaSalesHeader();

            //合計金額マイナス
            $dna_header->setTotalPrice($dna_header->getTotalPrice() - $dna_status->getPrice());

            //検査項目削除
            $details = $this->dnaSalesDetailRepository->findBy(["DnaSalesStatus" => $dna_status]);
            foreach($details as $detail){
                $em->remove($detail);
            }

            //明細削除
            $em->remove($dna_status);

            //合計0円の時はヘッダも削除
            if($dna_header->getTotalPrice() == 0){
                $em->remove($dna_header);
            }
            else{
                $em->persist($dna_header);
            }

            $em->flush();
        }

        return $this->redirect($this->generateUrl('dna_ec_top'));
    }

    /**
     * @Route("/dna/get_pet_type/{id}", name="dna_ec_getpet")
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

        $responce = [];
        foreach($breeds as $breed){
            $responce[] = ["id" => $breed->getId(),"breeds_name" => $breed->getBreedsName()];
        }
        //return [];
        return new JsonResponse($responce);
    }

    /**
     * @Route("/dna/get_dna_kinds/{id}", name="dna_ec_detkind")
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
