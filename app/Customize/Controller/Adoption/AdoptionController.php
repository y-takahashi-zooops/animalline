<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreedsRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PrefRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Service\AdoptionQueryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;
use Eccube\Event\EccubeEvents;
use Eccube\Service\MailService;

class AdoptionController extends AbstractController
{
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
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHousesRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository ,
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param BreedsRepository $breedsRepository
     * @param PrefRepository $prefRepository
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationsHousesRepository $conservationsHousesRepository
     * @param MailService $mailService
     */
    public function __construct(
        ConservationPetsRepository     $conservationPetsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationContactsRepository $conservationContactsRepository,
        AdoptionQueryService           $adoptionQueryService,
        PetsFavoriteRepository         $petsFavoriteRepository,
        BreedsRepository               $breedsRepository,
        PrefRepository                 $prefRepository,
        ConservationsRepository        $conservationsRepository,
        ConservationsHousesRepository  $conservationsHousesRepository,
        MailService                    $mailService
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breedsRepository = $breedsRepository;
        $this->prefRepository = $prefRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationsHousesRepository = $conservationsHousesRepository;
        $this->mailService = $mailService;
    }

    /**
     * Page Adoption
     *
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/adoption/reg_index_tmp.twig")
     */
    public function breeder_index_reg(Request $request)
    {
        return[];
    }

    /**
     * adoption index
     *
     * @Route("/adoption_tmp/", name="adoption_top_tmp")
     * @Template("animalline/adoption/index.twig")
     */
    public function adoption_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->adoptionQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->conservationPetsRepository->findBy(
            ['pet_kind' => $petKind, 'release_status' => AnilineConf::RELEASE_STATUS_PUBLIC],
            ['update_date' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );
        $favoritePets = $this->conservationPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['favorite_count' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );

        return $this->render('animalline/adoption/index.twig', [
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'newPets' => $newPets,
            'favoritePets' => $favoritePets,
        ]);
    }

    /**
     * @Route("/adoption/guide/dog", name="adoption_guide_dog")
     * @Template("animalline/adoption/guide/dog.twig")
     */
    public function adoption_guide_dog()
    {
        return [];
    }

    /**
     * @Route("/adoption/guide/cat", name="adoption_guide_cat")
     * @Template("animalline/adoption/guide/cat.twig")
     */
    public function adoption_guide_cat()
    {
        return [];
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
        }

        $handling_pet_kind = $conservation->getHandlingPetKind();
        $dogHouse = $this->conservationsHousesRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 1]);
        $catHouse = $this->conservationsHousesRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 2]);

        $petResults = $this->conservationPetsRepository->findBy([
            'Conservation' => $conservation,
            'release_status' => AnilineConf::RELEASE_STATUS_PUBLIC
        ]);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return compact(
            'conservation',
            'dogHouse',
            'catHouse',
            'pets'
        );
    }

    /**
     * 会社概要.
     *
     * @Route("/adoption/company", name="adoption_company")
     * @Template("animalline/adoption/company.twig")
     */
    public function company(Request $request)
    {
        return;
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
        return;
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
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $this->render('animalline/adoption/ani_contact_confirm.twig', [
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
                    return $this->render('animalline/adoption/ani_contact_complete.twig');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
