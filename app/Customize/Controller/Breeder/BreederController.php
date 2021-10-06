<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Service\BreederQueryService;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        PrefRepository            $prefRepository
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
    }

    /**
     * @Route("/breeder/reg", name="breeder_reg")
     * @Template("animalline/breeder/reg_index.twig")
     */
    public function breeder_index_reg(Request $request)
    {
        return[];
    }

    /**
     * @Route("/breeder/", name="breeder_top")
     * @Template("animalline/breeder/index.twig")
     */
    public function breeder_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->breederQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->breederPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['release_date' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );
        $favoritePets = $this->breederPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['favorite_count' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );

        return $this->render('animalline/breeder/index.twig', [
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'newPets' => $newPets,
            'favoritePets' => $favoritePets,
        ]);
    }
    
    /**
     * @Route("/breeder/guide/dog", name="breeder_guide_dog")
     * @Template("animalline/breeder/guide/dog.twig")
     */
    public function breeder_guide_dog()
    {
        return [];
    }

    /**
     * @Route("/breeder/guide/cat", name="breeder_guide_cat")
     * @Template("animalline/breeder/guide/cat.twig")
     */
    public function breeder_guide_cat()
    {
        return [];
    }

    /**
     * ブリーダー用ユーザーページ
     *
     * @Route("/breeder/member/", name="breeder_mypage")
     * @Template("animalline/breeder/member/index.twig")
     */
    public function breeder_mypage(Request $request)
    {
        $rootMessages = $this->breederContactsRepository
            ->findBy(
                [
                    'Customer' => $this->getUser(),
                    'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                    'contract_status' => AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION
                ]
            );

        $lastReplies = [];
        foreach ($rootMessages as $rootMessage) {
            $lastReply = $this->breederContactsRepository
                ->findOneBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'DESC']);
            $lastReplies[$rootMessage->getId()] = $lastReply;
        }

        $pets = $this->breederQueryService->findBreederFavoritePets($this->getUser()->getId());

        return $this->render('animalline/breeder/member/index.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies,
            'pets' => $pets,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * ペット詳細
     *
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $breederPet = $this->breederPetsRepository->find($id);
        $petKind = $breederPet->getPetKind();
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$breederPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $this->breederPetImageRepository->findBy(
            [
                'BreederPets' => $breederPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->breederPetImageRepository->findOneBy(
            [
                'BreederPets' => $breederPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );

        return $this->render(
            'animalline/breeder/pet/detail.twig',
            [
                'breederPet' => $breederPet,
                'petKind' => $petKind,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn
            ]
        );
    }

    /**
     * Page favorite pet
     * 
     * @Route("/breeder/pet/detail/favorite_pet", name="breeder_favorite_pet")
     */
    public function favoritePet(Request $request)
    {
        $id = $request->get('id');
        $pet = $this->breederPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$favorite) {
            $petKind = $pet->getPetKind();
            $favorite_pet = new PetsFavorite();
            $favorite_pet->setCustomer($this->getUser())
                ->setPetId($id)
                ->setSiteCategory(AnilineConf::SITE_CATEGORY_BREEDER)
                ->setPetKind($petKind);
            $entityManager->persist($favorite_pet);
            $entityManager->flush();

            $this->breederPetsRepository->incrementCount($pet);
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();

            $this->breederPetsRepository->decrementCount($pet);

            return new JsonResponse('unliked');
        }

        return new JsonResponse('liked');
    }

    /**
     * @Route("/breeder/breeder_search/{breeder_id}", name="breeder_detail", requirements={"breeder_id" = "\d+"})
     * @Template("/animalline/breeder/breeder_detail.twig")
     */
    public function breeder_detail(Request $request, $breeder_id, PaginatorInterface $paginator)
    {
        $breeder = $this->breedersRepository->find($breeder_id);
        if (!$breeder) throw new NotFoundHttpException();

        $handling_pet_kind = $breeder->getHandlingPetKind();
        $dogHouse = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
        $catHouse = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

        $petResults = $this->breederPetsRepository->findBy([
            'Breeder' => $breeder,
            'release_status' => AnilineConf::RELEASE_STATUS_PUBLIC
        ]);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return compact(
            'breeder',
            'dogHouse',
            'catHouse',
            'pets'
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
}
