<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Service\DnaQueryService;
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

class AdoptionSearchController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHouseRepository;

    /**
     * @var DnaQueryService;
     */
    protected $dnaQueryService;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var AdoptionQueryService
     */
    private $adoptionQueryService;

    /**
     * ConservationController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationsHousesRepository $conservationsHouseRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param BreedsRepository $breedsRepository
     * @param PrefRepository $prefRepository
     */
    public function __construct(
        ConservationsRepository             $conservationsRepository,
        ConservationsHousesRepository       $conservationsHouseRepository,
        AdoptionQueryService                $adoptionQueryService,
        BreedsRepository                    $breedsRepository,
        PrefRepository                      $prefRepository
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationsHouseRepository = $conservationsHouseRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->breedsRepository = $breedsRepository;
        $this->prefRepository = $prefRepository;
    }

    /**
     * 保護団体検索
     *
     * @Route("/adoption/adoption_search", name="adoption_search")
     * @Template("/animalline/adoption/adoption_search.twig")
     */
    public function adoption_search(PaginatorInterface $paginator, Request $request): Response
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->adoptionQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $adoptionResults = $this->adoptionQueryService->searchAdoptionsResult($request, $petKind);
        $adoptions = $paginator->paginate(
            $adoptionResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/adoption/adoption_search.twig', [
            'adoptions' => $adoptions,
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions
        ]);
    }

    /**
     * ペット検索結果.
     *
     * @Route("/adoption/pet/search/result", name="adoption_pet_search_result")
     * @Template("animalline/adoption/pet/search_result.twig")
     */
    public function petSearchResult(PaginatorInterface $paginator, Request $request): Response
    {
        $petResults = $this->adoptionQueryService->searchPetsResult($request);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->adoptionQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();

        return $this->render('animalline/adoption/pet/search_result.twig', [
            'pets' => $pets,
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions
        ]);
    }

    /**
     * pet data by pet kind
     *
     * @Route("/pet_data_by_pet_kind", name="pet_data_by_pet_kind", methods={"GET"})
     * @param Request $request
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @return JsonResponse
     */
    public function petDataByPetKind(Request $request, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository)
    {
        $petKind = $request->get('pet_kind');
        // $breeds = $breedsRepository->findBy(['pet_kind' => $petKind], ['breeds_name' => 'ASC']);
        $breeds = $breedsRepository->findBy(['pet_kind' => $petKind], ['sort_order' => 'ASC']);
        $colors = $coatColorsRepository->findBy(['pet_kind' => $petKind]);
        $formattedBreeds = [];
        foreach ($breeds as $breed) {
            $formattedBreeds[] = [
                'id' => $breed->getId(),
                'name' => $breed->getBreedsName()
            ];
        }
        $formattedColors = [];
        foreach ($colors as $color) {
            $formattedColors[] = [
                'id' => $color->getId(),
                'name' => $color->getCoatColorName()
            ];
        }
        $data = [
            'breeds' => $formattedBreeds,
            'colors' => $formattedColors
        ];

        return new JsonResponse($data);
    }
}
