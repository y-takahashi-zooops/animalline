<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Service\AdoptionQueryService;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Entity\AffiliateStatus;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\AffiliateStatusRepository;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Front\ContactType;
use Customize\Service\MailService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;
    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHousesRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var AffiliateStatusRepository
     */
    protected $affiliateStatusRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository ,
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param PrefRepository $prefRepository
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationsHousesRepository $conservationsHousesRepository
     * @param MailService $mailService
     * @param AffiliateStatusRepository $affiliateStatusRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConservationPetsRepository     $conservationPetsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationContactsRepository $conservationContactsRepository,
        AdoptionQueryService           $adoptionQueryService,
        PetsFavoriteRepository         $petsFavoriteRepository,
        PrefRepository                 $prefRepository,
        ConservationsRepository        $conservationsRepository,
        ConservationsHousesRepository  $conservationsHousesRepository,
        MailService                    $mailService,
        AffiliateStatusRepository $affiliateStatusRepository,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->prefRepository = $prefRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationsHousesRepository = $conservationsHousesRepository;
        $this->mailService = $mailService;
        $this->affiliateStatusRepository = $affiliateStatusRepository;
        $this->eventDispatcher = $eventDispatcher;
        
        // 親クラスのsetterメソッドを呼び出してプロパティを設定
        $this->setEntityManager($entityManager);
        $this->setFormFactory($formFactory);
    }


    /**
     * Page Adoption
     *
     * @Route("/adoption_reg/", name="adoption_top_reg")
     * @Template("animalline/adoption/reg_index_tmp.twig")
     */
    public function breeder_index_reg(Request $request)
    {
        return[];
    }

    /**
     * adoption index
     *
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/adoption/index.twig")
     */
    public function adoption_index(Request $request,PaginatorInterface $paginator)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->adoptionQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->adoptionQueryService->getPetNew($petKind);
        $favoritePets = $this->adoptionQueryService->getPetFeatured($petKind);

        
        $maintitle = "保護犬・猫の里親募集 アニマルライン";
        $breadcrumb = array(
            array('url' => $this->generateUrl('adoption_top'),'title' =>"保護団体TOP")
        );

        //紹介コード付きアクセスの場合
        $response = new Response();
        $rid = $request->get('RID');
        if($rid != ""){
            $sessid = $request->cookies->get('rid_key');
            //if($sessid == ""){
                $sessid = uniqid();
                $response->headers->setCookie(new Cookie('rid_key',$sessid));
            //}

            $entityManager = $this->entityManager;

            //$session = $request->getSession();
            //$sessid = $session->getId();

            $affiliate = $this->affiliateStatusRepository->findOneBy(array("campaign_id" => 2,"session_id" => $sessid));

            //if(!$affiliate){
                $affiliate = new AffiliateStatus();
            //}
            $affiliate->setAffiliateKey($rid);
            $affiliate->setCampaignId(2);
            $affiliate->setSessionId($sessid);
            $entityManager->persist($affiliate);
            $entityManager->flush();
        }

        //RSS
        $url = "https://animalline.jp/contents/feed/";
        $xml = file_get_contents($url);
        $obj = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
        $i = 0;
        foreach ($obj->channel->item as $val) {
            $skip=false;
            foreach($val->category as $cat){
                if($cat == "ブリーダー"){
                    $skip=true;
                }
            }
            if(!$skip){
                if ($i < 5){
                    $rssfeed[$i]['title'] = $val->title;
                    $rssfeed[$i]['link'] = $val->link;
                    $rssfeed[$i]['date'] = $val->pubDate;
                    $rssfeed[$i]['description'] = $val->description;
                    $rssfeed[$i]['category'] = $val->category;
                }
                $i++;
            }
        }

        //最新の犬猫データ最大12件
        $pets = $paginator->paginate(
            $newPets,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('animalline/adoption/index.twig', [
            'title' => 'ペット検索',
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'newPets' => $pets,
            'favoritePets' => $favoritePets,
            'maintitle' => $maintitle,
            'breadcrumb' => $breadcrumb,
            'rssfeed' => $rssfeed
        ],$response);
    }

    /**
     * @Route("/adoption/info", name="adoption_info")
     * @Template("animalline/adoption/info.twig")
     */
    public function adoption_info()
    {
        return [];
    }

    /**
     * @Route("/adoption/guide/dog", name="adoption_guide_dog")
     * @Template("animalline/adoption/guide/dog.twig")
     */
    public function adoption_guide_dog()
    {
        $title = "お迎えガイド（犬編）";

        return ['title'  => $title];
    }

    /**
     * @Route("/adoption/guide/cat", name="adoption_guide_cat")
     * @Template("animalline/adoption/guide/cat.twig")
     */
    public function adoption_guide_cat()
    {
        $title = "お迎えガイド（猫編）";

        return ['title'  => $title];
    }

    /**
     * 保護団体マイページ
     *
     * @Route("/adoption/member/", name="adoption_mypage")
     * @Template("animalline/adoption/member/index.twig")
     */
    public function adoption_mypage(Request $request)
    {
        return [];
    }

    /**
     * 保護団体詳細
     *
     * @Route("/adoption/adoption_search/{adoption_id}", name="adoption_detail", requirements={"adoption_id" = "\d+"})
     * @Template("/animalline/adoption/adoption_detail.twig")
     */
    public function adoption_detail(Request $request, $adoption_id, PaginatorInterface $paginator)
    {
        $conservation = $this->conservationsRepository->find($adoption_id);
        if (!$conservation) {
            throw new NotFoundHttpException();
        }

        $handling_pet_kind = $conservation->getHandlingPetKind();
        $dogHouse = $this->conservationsHousesRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 1]);
        $catHouse = $this->conservationsHousesRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 2]);

        /*
        $petResults = $this->conservationPetsRepository->findBy([
            'Conservation' => $conservation,
            'is_active' => AnilineConf::IS_ACTIVE_PUBLIC
        ]);
        */
        $petResults = $this->adoptionQueryService->searchPetsByConservation($conservation);

        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        $entityManager = $this->entityManager;
        $conservation->setViewCount(intval($conservation->getViewCount() + 1));
        $entityManager->persist($conservation);
        $entityManager->flush();

        return[
            'title' => '保護団体検索',
            'conservation' => $conservation,
            'dogHouse' => $dogHouse,
            'catHouse' => $catHouse,
            'pets' => $pets,
        ];

        /*
        return compact(
            'conservation',
            'dogHouse',
            'catHouse',
            'pets'
        );
        */

    }

    /**
     * 会社概要.
     *
     * @Route("/adoption/company", name="adoption_company")
     * @Template("animalline/adoption/company.twig")
     */
    public function company(Request $request)
    {
        return['title' => '会社概要',];
    }
    
    /**
     * お迎え時の費用について.
     *
     * @Route("/adoption/costinfo", name="adoption_costinfo")
     * @Template("animalline/adoption/costinfo.twig")
     */
    public function costinfo(Request $request)
    {
        $maintitle = "お迎え時の費用について";
        $breadcrumb = array(
            array('url' => $this->generateUrl('adoption_top'),'title' =>"保護団体TOP"),
            array('url' => $this->generateUrl('adoption_costinfo'),'title' =>"お迎え時の費用について")
        );

        return['title' => 'お迎え時の費用について',
            'maintitle' => $maintitle,
            'breadcrumb' => $breadcrumb,
        ];
    }

    /**
     * お引渡しの流れ.
     *
     * @Route("/adoption/transferinfo", name="adoption_transferinfo")
     * @Template("animalline/adoption/transferinfo.twig")
     */
    public function transferinfo(Request $request)
    {
        $maintitle = "お引渡しの流れ";
        $breadcrumb = array(
            array('url' => $this->generateUrl('adoption_top'),'title' =>"保護団体TOP"),
            array('url' => $this->generateUrl('adoption_transferinfo'),'title' =>"お引渡しの流れ")
        );

        return['title' => 'お引渡しの流れ',
            'maintitle' => $maintitle,
            'breadcrumb' => $breadcrumb,
        ];
    }

    /**
     * 特定商取引法に基づく表記.
     *
     * @Route("/adoption/tradelaw", name="adoption_tradelaw")
     * @Template("animalline/adoption/tradelaw.twig")
     */
    public function tradelaw(Request $request)
    {
        return;
    }
    
    /**
     * プライバシーポリシー.
     *
     * @Route("/adoption/policy", name="adoption_policy")
     * @Template("animalline/adoption/policy.twig")
     */
    public function policy(Request $request)
    {
        $title = "個人情報保護方針";

        return ['title'  => $title];
    }
    
    /**
     * 利用規約.
     *
     * @Route("/adoption/terms", name="adoption_terms")
     * @Template("animalline/adoption/terms.twig")
     */
    public function terms(Request $request)
    {
        return;
    }
    
    /**
     * 問い合わせ.
     *
     * @Route("/adoption/ani_contact", name="adoption_ani_contact")
     * @Template("animalline/adoption/ani_contact.twig")
     */
    public function ani_contact(Request $request)
    {
        $builder = $this->formFactory->createBuilder(ContactType::class);

        if ($this->isGranted('ROLE_USER')) {
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

        $newFilename = $request->get("newFilename");
        if ($form->isSubmitted()) {
            $brochureFile = $form->get('files')->getData();
            
            if($brochureFile){
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = 'contact-'.uniqid().'.'.$brochureFile->guessExtension();

                $brochureFile->move(
                    "var/tmp/contact/",
                    $newFilename
                );

                $builder->setData(["files" => "var/tmp/contact/".$newFilename]);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $this->render('animalline/adoption/ani_contact_confirm.twig', [
                        'form' => $form->createView(),
                        "newFilename" => $newFilename
                    ]);

                case 'complete':

                    $data = $form->getData();

                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'data' => $data,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_CONTACT_INDEX_COMPLETE);

                    $data = $event->getArgument('data');

                    // メール送信
                    $this->mailService->sendContactMail($data,$newFilename);

                    // return $this->redirect($this->generateUrl('contact_complete'));
                    return $this->render('animalline/adoption/ani_contact_complete.twig');
            }
        }

        $title = "お問い合わせ";

        return [
            'title'  => $title,
            'form' => $form->createView(),
            "newFilename" => $newFilename
        ];
    }
}
