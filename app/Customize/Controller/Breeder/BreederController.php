<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Service\BreederQueryService;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;
use Customize\Service\MailService;

class BreederController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;
    /**
     * @var BreederHouseRepository
     */
    protected $breederHouseRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * BreederController constructor.
     *
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreedersRepository $breedersRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param BreederEvaluationsRepository $breederEvaluationsRepository
     * @param MailService $mailService
     */
    public function __construct(
        BreederContactsRepository $breederContactsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederQueryService       $breederQueryService,
        PetsFavoriteRepository    $petsFavoriteRepository,
        SendoffReasonRepository   $sendoffReasonRepository,
        BreedersRepository        $breedersRepository,
        BreederHouseRepository    $breederHouseRepository,
        BreederPetsRepository     $breederPetsRepository,
        PrefRepository            $prefRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        BreederEvaluationsRepository $breederEvaluationsRepository,
        MailService                      $mailService
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breedersRepository = $breedersRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->prefRepository = $prefRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
        $this->mailService = $mailService;
    }


    /**
     * Page Breeder
     *
     * @Route("/breeder_reg/", name="breeder_top_reg")
     * @Template("animalline/breeder/reg_index.twig")
     */
    public function breeder_index_reg(Request $request)
    {
        return [];
    }

    /**
     * Page Breeder
     *
     * @Route("/breeder/", name="breeder_top")
     * @Template("animalline/breeder/index.twig")
     */
    public function breeder_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->breederQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->breederQueryService->getPetNew($petKind);
        $favoritePets = $this->breederQueryService->getPetFeatured($petKind);

        return $this->render('animalline/breeder/index.twig', [
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'newPets' => $newPets,
            'favoritePets' => $favoritePets,
        ]);
    }

    /**
     * @Route("/breeder/info", name="breeder_info")
     * @Template("animalline/breeder/info.twig")
     */
    public function breeder_info()
    {
        return [];
    }



    /**
     * ブリーダーマイページ
     *
     * @Route("/breeder/member/", name="breeder_mypage")
     * @Template("animalline/breeder/member/index.twig")
     */
    public function breeder_mypage(Request $request)
    {
        return [];
    }

    /**
     * サイトイメージ（スマホ）
     *
     * @Route("/breeder/simage_sp/", name="breeder_simage_sp")
     * @Template("animalline/breeder/simage_sp.twig")
     */
    public function simage_sp(Request $request)
    {
        return [];
    }

    /**
     * サイトイメージ（スマホ）
     *
     * @Route("/breeder/simage_pc/", name="breeder_simage_pc")
     * @Template("animalline/breeder/simage_pc.twig")
     */
    public function simage_pc(Request $request)
    {
        return [];
    }

    /**
     * ブリーダー詳細
     *
     * @Route("/breeder/breeder_search/{breeder_id}", name="breeder_detail", requirements={"breeder_id" = "\d+"})
     * @Template("/animalline/breeder/breeder_detail.twig")
     */
    public function breeder_detail(Request $request, $breeder_id, PaginatorInterface $paginator)
    {
        $breeder = $this->breedersRepository->find($breeder_id);
        if (!$breeder) {
            throw new NotFoundHttpException();
        }

        $handling_pet_kind = $breeder->getHandlingPetKind();
        $dogHouse = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
        $catHouse = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

        $petResults = $this->breederPetsRepository->findBy([
            'Breeder' => $breeder
        ]);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        $allEvaluations = $this->breederEvaluationsRepository->findBy(['Breeder' => $breeder, 'is_active' => 2], ['create_date' => 'DESC']);
        $evaluationCount = count($allEvaluations);
        $evaluations = $this->breederEvaluationsRepository->findBy(['Breeder' => $breeder, 'is_active' => 2], ['create_date' => 'DESC'], 3);

        return compact(
            'breeder',
            'dogHouse',
            'catHouse',
            'pets',
            'evaluations',
            'evaluationCount'
        );
    }

    
    /**
     * 評価一覧
     *
     * @Route("/breeder/evaluation/{breeder_id}", name="breeder_evaluation", requirements={"breeder_id" = "\d+"})
     * @Template("/animalline/breeder/breeder_evaluation.twig")
     */
    public function breeder_evaluation(Request $request, $breeder_id, PaginatorInterface $paginator)
    {
        $breeder = $this->breedersRepository->find($breeder_id);
        if (!$breeder) {
            throw new NotFoundHttpException();
        }

        $evaluationsResult = $this->breederEvaluationsRepository->findBy(['Breeder' => $breeder, 'is_active' => 2], ['create_date' => 'DESC']);
        $evaluations = $paginator->paginate(
            $evaluationsResult,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
       
        return compact(
            'breeder',
            'evaluations'
        );
    }

    /**
     * 会社概要.
     *
     * @Route("/breeder/company", name="breeder_company")
     * @Template("animalline/breeder/company.twig")
     */
    public function company(Request $request)
    {
        return;
    }

    /**
     * 特定商取引法に基づく表記.
     *
     * @Route("/breeder/tradelaw", name="breeder_tradelaw")
     * @Template("animalline/breeder/tradelaw.twig")
     */
    public function tradelaw(Request $request)
    {
        return;
    }

    /**
     * プライバシーポリシー.
     *
     * @Route("/breeder/policy", name="breeder_policy")
     * @Template("animalline/breeder/policy.twig")
     */
    public function policy(Request $request)
    {
        return;
    }

    /**
     * 利用規約.
     *
     * @Route("/breeder/terms", name="breeder_terms")
     * @Template("animalline/breeder/terms.twig")
     */
    public function terms(Request $request)
    {
        return;
    }

    /**
     * 問い合わせ.
     *
     * @Route("/breeder/ani_contact", name="breeder_ani_contact")
     * @Template("animalline/breeder/ani_contact.twig")
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
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $this->render('animalline/breeder/ani_contact_confirm.twig', [
                        'form' => $form->createView(),
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
                    $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_COMPLETE, $event);

                    $data = $event->getArgument('data');

                    // メール送信
                    $this->mailService->sendContactMail($data);

                    // return $this->redirect($this->generateUrl('contact_complete'));
                    return $this->render('animalline/breeder/ani_contact_complete.twig');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
