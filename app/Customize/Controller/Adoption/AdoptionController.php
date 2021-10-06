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
        ConservationsHousesRepository  $conservationsHousesRepository
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
    }

    /**
     * adoption index
     *
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/adoption/index.twig")
     */
    public function adoption_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->adoptionQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->conservationPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['release_date' => 'DESC'],
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
     * 保護団体用ユーザーページ
     *
     * @Route("/adoption/member/", name="adoption_mypage")
     * @Template("animalline/adoption/member/index.twig")
     */
    public function adoption_mypage(Request $request)
    {
        $customerId = $this->getUser()->getId();
        $rootMessages = $this->conservationContactsRepository
            ->findBy(
                [
                    'Customer' => $customerId,
                    'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                    'contract_status' => AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION
                ]
            );

        $lastReplies = [];
        foreach ($rootMessages as $rootMessage) {
            $lastReply = $this->conservationContactsRepository
                ->findOneBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'DESC']);
            $lastReplies[$rootMessage->getId()] = $lastReply;
        }

        return $this->render('animalline/adoption/member/index.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies
        ]);
    }

    /**
     * よくある質問.
     *
     * @Route("/adoption/faq", name="adoption_faq")
     * @Template("animalline/adoption/faq.twig")
     */
    public function faq(Request $request)
    {
        return;
    }

    /**
     * サイト説明。初めての方へ.
     *
     * @Route("/adoption/readfirst", name="adoption_readfirst")
     * @Template("animalline/adoption/readfirst.twig")
     */
    public function readfirst(Request $request)
    {
        return;
    }

    /**
     * 最近見た子犬.
     *
     * @Route("/adoption/viewhist", name="adoption_viewhist")
     * @Template("animalline/adoption/viewhist.twig")
     */
    public function viewhist(Request $request)
    {
        return;
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
        if (!$conservation) throw new NotFoundHttpException();

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
     * 保護団体リスト.
     *
     * @Route("/adoption/list", name="adoption_list")
     * @Template("animalline/adoption/list.twig")
     */
    public function list(Request $request)
    {
        return;
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
}
