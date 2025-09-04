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
use Eccube\Repository\CategoryRepository;
use Customize\Config\AnilineConf;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(
        NewsRepository $NewsRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ProductListOrderByRepository $productListOrderByRepository,
        MailService $mailService,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->NewsRepository = $NewsRepository;
        $this->productListOrderByRepository = $productListOrderByRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->mailService = $mailService;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
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

        //新着
        $searchData["category_id"] = null;
        $searchData['orderby'] = $this->productListOrderByRepository->find(2);
        $qb = $this->productRepository->getQueryBuilderBySearchData($searchData);
        $query = $qb->getQuery();
        $products_new = $query->getResult();

        //おすすめ
        $searchData["category_id"] = $this->categoryRepository->find(49);
        //$searchData['orderby'] = $this->productListOrderByRepository->find(2);
        $qb = $this->productRepository->getQueryBuilderBySearchData($searchData);
        $query = $qb->getQuery();
        $osusume = $query->getResult();

        return [
            'categoryChildDog' => $categoryChildDog,
            'categoryChildCat' => $categoryChildCat,
            'categoryChildBeside' => $categoryChildBeside,
            'products_new' => $products_new,
            'osusume' => $osusume
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
        
        $title = "TOPページ";

        return [
            'ListNews' => $ListNews,
            'title'  => $title
        ];
    }

    /**
     * @Route("/d", name="dna_byt_regpet")
     */
    public function dna_byt_regpet()
    {
        return $this->redirectToRoute("mypage_dna");
    }

    /**
     * @Route("/tr", name="ani_training")
     * @Template("ani_training.twig")
     */
    public function animalline_training()
    {   
        /*
        $customer = $this->getUser();

        if(!$customer){
            return $this->redirectToRoute("breeder_login");
        }
        */

        $title = "成約特典について";

        return ['title'  => $title];
    }

    /**
     * Entry Training
     *
     * @Route("/tr/entry", name="ani_entry_training")
     * @Template("ani_entry_training.twig")
     */
    public function animallineEntryTraining(Request $request)
    {
        $builder = $this->formFactory->createBuilder(TrainingType::class);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->mailService->sendNotifyReceiveSeminarRegistered($data);

            return $this->redirectToRoute('ani_training');
        }

        return [
            'form' => $form->createView()
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
        $title = "利用規約";

        return ['title'  => $title];
    }

    /**
     * 利用規約
     * 
     * @Route("/aterms", name="adoption_user_terms")
     * @Template("adoption_user_terms.twig")
     */
    public function adoption_terms()
    {
        $title = "利用規約";

        return ['title'  => $title];
    }

    /**
     * プライバシーポリシー
     * 
     * @Route("/policy", name="ani_policy")
     * @Template("ani_policy.twig")
     */
    public function policy()
    {
        $title = "プライバシーポリシー";

        return ['title'  => $title];
    }

    /**
     * 特定商取引法に基づく表記
     * 
     * @Route("/tradelaw", name="ani_tradelaw")
     * @Template("ani_tradelaw.twig")
     */
    public function tradelaw()
    {
        $title = "特定商取引法に基づく表記";

        return ['title'  => $title];
    }

    /**
     * 会社概要
     * 
     * @Route("/company", name="ani_company")
     * @Template("ani_company.twig")
     */
    public function company()
    {
        $title = "会社概要";

        return ['title'  => $title];
    }

    /**
     * ＤＮＡ検査の説明
     * 
     * @Route("/dna", name="ani_dna_info")
     * @Template("dnainfo.twig")
     */
    public function dna_detail(){
        $title = "ＤＮＡ検査について";

        return ['title'  => $title];
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
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE);

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
        $title = "お迎えガイド（犬編）";

        return ['title'  => $title];
    }

    /**
     * @Route("/guide/cat", name="breeder_guide_cat")
     * @Template("animalline/breeder/guide/cat.twig")
     */
    public function breeder_guide_cat()
    {
        $title = "お迎えガイド（猫編）";

        return ['title'  => $title];
    }

    /**
     * ニュートロ
     * 
     * @Route("/nutro", name="ec_nutro")
     * @Template("nutro.twig")
     */
    public function ec_nutro(){
        return [];
    }

    /**
     * Windowを閉じる
     * 
     * @Route("/close_window", name="close_window")
     * @Template("close_window.twig")
     */
    public function close_window(){
        return [];
    }
    
    /**
     * Windowを閉じる
     * 
     * @Route("/ajax/imageconvert", name="image_convert")
     */
    public function image_convert(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $data = $request->get('filedata');
        $name = $request->get('filename');

        if (!file_exists(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/')) {
            mkdir(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/', 0777, 'R');
        }

        $folderPath = AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/';
        
        
        $fileinfo = pathinfo($name);
        $filetype = mb_strtolower($fileinfo['extension']);

        $filename = uniqid();
        $file = $folderPath . $filename.".".$filetype;
        $image_parts = explode(";base64,", $data);
        $image_base64 = base64_decode($image_parts[1]);
        file_put_contents($file, $image_base64);
        
        if($filetype == "heic"){
            $base_folder = "/var/www/animalline/".$folderPath;
            $command = "heif-convert ". $base_folder.$filename.".".$filetype." ".$base_folder.$filename.".jpg";

            /*
            $imagick = new \Imagick();
            $imagick->readImage($base_folder.$filename.".".$filetype);
            $imagick->setImageFormat('jpg');
            $imagick->writeImage($base_folder.$filename.".jpg");

            $imagick->clear();
            $imagick->destroy();
            */
            shell_exec($command);

            $file = $folderPath . $filename.".jpg";
        }


        $url = "/".$file;
        $type = $filetype;
        
        return $this->json([
            'type' => $type,
            'url' => $url,
        ]);
    }
}
