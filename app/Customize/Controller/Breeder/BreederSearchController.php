<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Repository\BreedsRepository;
use Customize\Service\BreederQueryService;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class BreederSearchController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

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
     * @var BreedsRepository
     */
    protected $breedsRepository;
    

    /**
     * BreederController constructor.
     *
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreedersRepository $breedersRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreedsRepository $breedsRepository
     */
    public function __construct(
        BreederContactsRepository $breederContactsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederQueryService       $breederQueryService,
        PetsFavoriteRepository    $petsFavoriteRepository,
        SendoffReasonRepository   $sendoffReasonRepository,
        BreedersRepository        $breedersRepository,
        BreederPetsRepository     $breederPetsRepository,
        PrefRepository            $prefRepository,
        BreedsRepository          $breedsRepository
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->prefRepository = $prefRepository;
        $this->breedsRepository = $breedsRepository;
    }

    /**
     * @Route("/breeder/pet/search/result", name="breeder_pet_search_result")
     * @Template("animalline/breeder/pet/search_result.twig")
     */
    public function petSearchResult(PaginatorInterface $paginator, Request $request): Response
    {
        $petResults = $this->breederQueryService->searchPetsResult($request);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->breederQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();

        $breredname = "";
        $breedtype = $request->get('breed_type');
        $sizecode = $request->get('size_code');
        if($breedtype){
            $now_breeds = $this->breedsRepository->find($breedtype);
            $breredname = $now_breeds->getBreedsName();
        } 
        
        $title = "ペット検索結果";
        $maintitle = "ペット検索結果";
        if($breredname != ""){
            if($petKind == 1){
                $title = $breredname."の子犬一覧";
            }
            else{
                $title = $breredname."の子猫一覧";
            }
            $maintitle .= "(".$breredname.")";
        }
        $breadcrumb = array(
            array('url' => $this->generateUrl('breeder_top'),'title' =>"犬・猫ブリーダー直販サイトのアニマルライン "),
            array('url' => "#",'title' => $maintitle)
        );

        //犬の都道府県
        $prefs_dog = $this->breederQueryService->getActivePrefs(1);
        $pref_ids_dog = [];
        foreach ($prefs_dog as $pref) {
            $pref_ids_dog[] = $pref["id"];
            $pref_name_dog[$pref["id"]] = $pref["name"];
        }

        $prefs_dog_breeder = $this->breederQueryService->getBreederPrefs(1);
        $pref_ids_dog_breeder = [];
        foreach ($prefs_dog_breeder as $pref) {
            $pref_ids_dog_breeder[] = $pref["id"];
            $pref_name_dog_breeder[$pref["id"]] = $pref["name"];
        }

        //猫の都道府県
        $prefs_cat = $this->breederQueryService->getActivePrefs(2);
        $pref_ids_cat = [];
        foreach ($prefs_cat as $pref) {
            $pref_ids_cat[] = $pref["id"];
            $pref_name_cat[$pref["id"]] = $pref["name"];
        }
        
        $prefs_cat_breeder = $this->breederQueryService->getBreederPrefs(2);
        $pref_ids_cat_breeder = [];
        foreach ($prefs_cat_breeder as $pref) {
            $pref_ids_cat_breeder[] = $pref["id"];
            $pref_name_cat_breeder[$pref["id"]] = $pref["name"];
        }

        //猫種
        $breeds_cat = $this->breederQueryService->getBreedsHavePet(2);

        //犬種
        $breeds_dog = $this->breederQueryService->getBreedsHavePet(1);

        return $this->render('animalline/breeder/pet/search_result.twig', [
            'pets' => $pets,
            'petKind' => $petKind,
            'breedType' => $breedtype,
            'sizeCode' => $sizecode,
            'breeds' => $breeds,
            'regions' => $regions,
            'title' => $title,
            'maintitle' => $maintitle,
            'breadcrumb' => $breadcrumb,
            "description_add" => $breredname,
            'pref_name_dog' => $pref_name_dog,
            'pref_ids_dog' => $pref_ids_dog,
            'pref_name_cat' => $pref_name_cat,
            'pref_ids_cat' => $pref_ids_cat,
            'breeds_cat' => $breeds_cat,
            'breeds_dog' => $breeds_dog,
            'pref_ids_cat_breeder' => $pref_ids_cat_breeder,
            'pref_name_cat_breeder' => $pref_name_cat_breeder,
            'pref_ids_dog_breeder' => $pref_ids_dog_breeder,
            'pref_name_dog_breeder' => $pref_name_dog_breeder,
            'search_box_mode' => $petKind,
        ]);
    }

    /**
     * ブリーダー検索
     *
     * @Route("/breeder/breeder_search", name="breeder_search")
     * @Template("/animalline/breeder/breeder_search.twig")
     */
    public function breeder_search(PaginatorInterface $paginator, Request $request): Response
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->breederQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $breederResults = $this->breederQueryService->searchBreedersResult($request, $petKind);
        $breeders = $paginator->paginate(
            $breederResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        $maintitle = "ブリーダー検索";
        $breadcrumb = array(
            array('url' => $this->generateUrl('breeder_top'),'title' =>"犬・猫ブリーダー直販サイトのアニマルライン "),
            array('url' => "#",'title' => "ブリーダー検索")
        );

        return $this->render('animalline/breeder/breeder_search.twig', [
            'title' => 'ブリーダー検索',
            'breeders' => $breeders,
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'maintitle' => $maintitle,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Get pet data by pet kind
     *
     * @Route("/breeder_pet_data_by_pet_kind", name="breeder_pet_data_by_pet_kind", methods={"GET"})
     */
    public function breederPetDataByPetKind(Request $request, BreedsRepository $breedsRepository)
    {
        $petKind = $request->get('pet_kind');
        // $breeds = $breedsRepository->findBy(['pet_kind' => $petKind], ['breeds_name' => 'ASC']);
        $breeds = $breedsRepository->findBy(['pet_kind' => $petKind], ['sort_order' => 'ASC']);
        $formattedBreeds = [];
        foreach ($breeds as $breed) {
            $formattedBreeds[] = [
                'id' => $breed->getId(),
                'name' => $breed->getBreedsName()
            ];
        }

        $data = [
            'breeds' => $formattedBreeds
        ];

        return new JsonResponse($data);
    }
}
