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
use Symfony\Component\HttpFoundation\JsonResponse;

use Eccube\Service\CartService;
use Eccube\Repository\CartItemRepository;
use Eccube\Repository\CartRepository;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseContext;

class DnaEcController extends AbstractController
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
        PurchaseFlow $cartPurchaseFlow
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
    }


    /**
     * @Route("/ec/dna", name="dna_ec_top")
     * @Template("dna_ec.twig")
     */
    public function dna_ec()
    {
        $breeds = $this->breedsRepository->findAll();

        return [
            'breeds' => $breeds
        ];
    }

    /**
     * @Route("/ec/dna_buy", name="dna_buy")
     */
    public function dna_buy(Request $request)
    {
        $cnt = 0;
        for($i=1;$i<=6;$i++){
            if($request->get("check_kind_".$i) == "1"){
                $cnt++;
            }
        }

        /*
        $cartItem = $this->cartItemRepository->findOneBy(['Cart' => $this->cartService->getCarts(),'ProductClass' => $addCartData['product_class_id']]);
        if ($cartItem) {
            // 同商品がカートに存在したらカートに追加させない
            return;
        }
        */

        $product = $this->productClassRepository->findOneBy(["code" => "A001"]);
var_dump($product->getId());
        if(!$this->cartService->addProduct($product,1)){
            throw new HttpException\NotFoundHttpException();
            return;
        }
        // 明細の正規化
        /*
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
        */

        return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * @Route("/ec/dna/get_pet_type/{id}", name="dna_ec_getpet")
     */
    public function dna_ec_getpet($id)
    {
        $breeds = $this->breedsRepository->findBy(["pet_kind" => $id],["sort_order" => "ASC"]);

        $responce = [];
        foreach($breeds as $breed){
            $responce[] = ["id" => $breed->getId(),"breeds_name" => $breed->getBreedsName()];
        }
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
