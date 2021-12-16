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

use Carbon\Carbon;
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
use Eccube\Repository\CategoryRepository;

class CustomTopController extends AbstractController
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
     * @var ProductListOrderByRepository
     */
    protected $productListOrderByRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    public function __construct(
        NewsRepository $NewsRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ProductListOrderByRepository $productListOrderByRepository
    ) {
        $this->NewsRepository = $NewsRepository;
        $this->productListOrderByRepository = $productListOrderByRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }
    /**
     * @Route("/ec", name="homepage")
     * @Template("index.twig")
     */
    public function index()
    {
        $customer = $this->getUser();

        $categoryChildDog = $this->categoryRepository->getNumberOfProduct(7, $customer);
        $categoryChildCat = $this->categoryRepository->getNumberOfProduct(8, $customer);
        $categoryChildBeside = $this->categoryRepository->getNumberOfProduct(9, $customer);

        $searchData["category_id"] = null;
        $searchData['orderby'] = $this->productListOrderByRepository->find(2);

        $qb = $this->productRepository->getQueryBuilderBySearchData($searchData);

        $query = $qb->getQuery();
        $products_new = $query->getResult();

        //$searchData["category_id"] = 49;
        //$osusume = $this->productRepository->getQueryBuilderBySearchData($searchData);

        return [
            'categoryChildDog' => $categoryChildDog,
            'categoryChildCat' => $categoryChildCat,
            'categoryChildBeside' => $categoryChildBeside,
            'products_new' => $products_new
        ];
    }

    /**
     * @Route("/", name="ani_homepage")
     * @Template("ani_index.twig")
     */
    public function animalline_index()
    {
        $ListNews = $this->NewsRepository->getList();
        //return $this->redirectToRoute("breeder_top");
        
        return [
            'ListNews' => $ListNews
        ];
    }

    /**
     * 利用規約
     * 
     * @Route("/bterms", name="breeder_user_terms")
     * @Template("breeder_user_terms.twig")
     */
    public function breeder_terms()
    {
        return [];
    }

    /**
     * 利用規約
     * 
     * @Route("/aterms", name="adoption_user_terms")
     * @Template("adoption_user_terms.twig")
     */
    public function adoption_terms()
    {
        return [];
    }

    /**
     * プライバシーポリシー
     * 
     * @Route("/policy", name="ani_policy")
     * @Template("ani_policy.twig")
     */
    public function policy()
    {
        return [];
    }

    /**
     * 特定商取引法に基づく表記
     * 
     * @Route("/tradelaw", name="ani_tradelaw")
     * @Template("ani_tradelaw.twig")
     */
    public function tradelaw()
    {
        return [];
    }

    /**
     * 会社概要
     * 
     * @Route("/company", name="ani_company")
     * @Template("ani_company.twig")
     */
    public function company()
    {
        return [];
    }

    /**
     * ＤＮＡ検査の説明
     * 
     * @Route("/dna", name="ani_dna_info")
     * @Template("dnainfo.twig")
     */
    public function dna_detail(){
        return [];
    }

    /**
     * ＤＣＴＮの説明
     * 
     * @Route("/dctn", name="ani_dctn_info")
     * @Template("dctn_info.twig")
     */
    public function dctn_info(){
        return [];
    }

    /**
     * @Route("/contact", name="ani_contact")
     * @Template("ani_contact.twig")
     */
    public function contact(Request $request)
    {
        $builder = $this->formFactory->createBuilder(ContactType::class);

        if ($this->isGranted('ROLE_ADOPTION_USER')) {
            /** @var Customer $user */
            $user = $this->getUser();
            $builder->setData(
                [
                    'name01' => $user->getName01(),
                    'name02' => $user->getName02(),
                    'kana01' => $user->getKana01(),
                    'kana02' => $user->getKana02(),
                    'postal_code' => $user->getPostalCode(),
                    'pref' => $user->getPref(),
                    'addr01' => $user->getAddr01(),
                    'addr02' => $user->getAddr02(),
                    'phone_number' => $user->getPhoneNumber(),
                    'email' => $user->getEmail(),
                ]
            );
        }

        // FRONT_CONTACT_INDEX_INITIALIZE
        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);      

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/guide/dog", name="breeder_guide_dog")
     * @Template("animalline/breeder/guide/dog.twig")
     */
    public function breeder_guide_dog()
    {
        return [];
    }

    /**
     * @Route("/guide/cat", name="breeder_guide_cat")
     * @Template("animalline/breeder/guide/cat.twig")
     */
    public function breeder_guide_cat()
    {
        return [];
    }
}
