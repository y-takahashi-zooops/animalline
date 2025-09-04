<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Service\BreederQueryService;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\AffiliateStatusRepository;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Entity\AffiliateStatus;
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
use Customize\Form\Type\Front\ContactType;
use Customize\Service\MailService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
   * @var AffiliateStatusRepository
   */
  protected $affiliateStatusRepository;

  /**
   * @var EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var FormFactoryInterface
   */
  protected $formFactory;


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
   * @param AffiliateStatusRepository $affiliateStatusRepository
   */
  public function __construct(
    EntityManagerInterface $entityManager,
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
    MailService                      $mailService,
    AffiliateStatusRepository $affiliateStatusRepository,
    FormFactoryInterface $formFactory,
    EventDispatcherInterface $eventDispatcher
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
    $this->affiliateStatusRepository = $affiliateStatusRepository;
    $this->entityManager = $entityManager;
    $this->formFactory = $formFactory;
    $this->eventDispatcher = $eventDispatcher;
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

  // 成約済のステータスを取得する関数
  public function getIsDelete()
  {
    return $this->is_delete;
  }

  // DNA検査中のステータスを取得する関数
  public function getDnaCheckResult()
  {
    return $this->dna_check_result;
  }

  // 商談中のステータスを取得する関数
  public function getIsContact()
  {
    return $this->is_contact;
  }

  /**
   * Page Breeder
   *
   * @Route("/breeder/", name="breeder_top")
   * @Template("animalline/breeder/index.twig")
   */
  public function breeder_index(Request $request, PaginatorInterface $paginator)
  {
    $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
    $breeds = $this->breederQueryService->getBreedsHavePet($petKind);
    $regions = $this->prefRepository->findAll();
    $newDogs = $this->breederQueryService->getPetNew(AnilineConf::ANILINE_PET_KIND_DOG);
    $newCats = $this->breederQueryService->getPetNew(AnilineConf::ANILINE_PET_KIND_CAT);
    $favoritePets = $this->breederQueryService->getPetFeatured($petKind);

    $maintitle = "犬・猫ブリーダー直販のアニマルライン";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン ")
    );

    //紹介コード付きアクセスの場合
    $response = new Response();
    $rid = $request->get('RID');
    if ($rid != "") {
      $sessid = $request->cookies->get('rid_key');
      //if($sessid == ""){
      $sessid = uniqid();
      $response->headers->setCookie(new Cookie('rid_key', $sessid));
      //}

      $entityManager = $this->entityManager;

      //$session = $request->getSession();
      //$sessid = $session->getId();

      $affiliate = $this->affiliateStatusRepository->findOneBy(array("campaign_id" => 1, "session_id" => $sessid));

      //if(!$affiliate){
      $affiliate = new AffiliateStatus();
      //}
      $affiliate->setAffiliateKey($rid);
      $affiliate->setCampaignId(1);
      $affiliate->setSessionId($sessid);
      $entityManager->persist($affiliate);
      $entityManager->flush();
    }

    //最新の犬猫データ最大9件
    $newDog = $paginator->paginate(
      $newDogs,
      $request->query->getInt('page', 1),
      9
    );

    $newCat = $paginator->paginate(
      $newCats,
      $request->query->getInt('page', 1),
      9
    );

    //RSS
    $url = "https://animalline.jp/contents/feed/";
    $xml = file_get_contents($url);
    $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $i = 0;
    foreach ($obj->channel->item as $val) {
      $skip = false;
      foreach ($val->category as $cat) {
        if ($cat == "保護") {
          $skip = true;
        }
      }
      if (!$skip) {
        if ($i < 4) {
          $rssfeed[$i]['title'] = $val->title;
          $rssfeed[$i]['link'] = $val->link;
          $rssfeed[$i]['date'] = $val->pubDate;
          $rssfeed[$i]['description'] = $val->description;
          $rssfeed[$i]['category'] = $val->category;
        }
        $i++;
      }
    }

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

    //今日の日付
    $today = new DateTime();
    $pets_count_inspection = count($this->breederPetsRepository->findBy(["is_active" => 1, "dna_check_result" => 1]));

    // ブリーダートップページの販売中のワンちゃんとネコちゃんを取得する処理
    // getIsDelete = 成約済のステータス, getDnaCheckResult = DNA検査中のステータス, getIsContact = 商談中のステータス
    // 犬の条件を満たすものをフィルタリング
    $filteredDogs = array_filter($newDogs, function ($dog) {
      return $dog->getIsDelete() !== 1 && $dog->getDnaCheckResult() !== 0 && $dog->getIsContact() !== 1;
    });

    // 猫の条件を満たすものをフィルタリング
    $filteredCats = array_filter($newCats, function ($cat) {
      return $cat->getIsDelete() !== 1 && $cat->getDnaCheckResult() !== 0 && $cat->getIsContact() !== 1;
    });

    // 犬からランダムに3匹取得
    $randomDogs = [];
    if (count($filteredDogs) >= 3) {
      $dogKeys = array_rand($filteredDogs, 3);
      foreach ($dogKeys as $key) {
        $randomDogs[] = $filteredDogs[$key];
      }
    } else {
      $randomDogs = $filteredDogs;
    }

    // 猫からランダムに3匹取得
    $randomCats = [];
    if (count($filteredCats) >= 3) {
      $catKeys = array_rand($filteredCats, 3);
      foreach ($catKeys as $key) {
        $randomCats[] = $filteredCats[$key];
      }
    } else {
      $randomCats = $filteredCats;
    }

    // 2つのランダムな配列をマージ
    $randomPets = array_merge($randomDogs, $randomCats);
    shuffle($randomPets);

    return $this->render('animalline/breeder/index.twig', [
      // 'title' => 'ペット検索',
      'petKind' => $petKind,
      'breeds' => $breeds,
      'regions' => $regions,
      'newDog' => $newDog,
      'newCat' => $newCat,
      'favoritePets' => $favoritePets,
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
      'rssfeed' => $rssfeed,
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
      'today' => $today,
      'pets_count_inspection' => $pets_count_inspection,
      'search_box_mode' => 9,
      'randomPets' => $randomPets,
    ], $response);
  }

  /**
   * @Route("/breeder/ajax/get_breeds_by_pref", name="breeder_ajax_get_breeds_by_pref")
   */
  public function breeder_ajax_get_breeds_by_pref(Request $request): JsonResponse
  {
    if (!$request->isXmlHttpRequest()) {
      throw new BadRequestHttpException();
    }

    $json = file_get_contents('php://input');

    $params = json_decode($json);

    $breeds = $this->breederQueryService->getBreedsByPref($params->prefs, $params->pet_kind);

    return new JsonResponse($breeds);
  }

  /**
   * @Route("/breeder/ajax/get_breeds_by_size", name="breeder_ajax_get_breeds_by_size")
   */
  public function breeder_ajax_get_breeds_by_size(Request $request): JsonResponse
  {
    if (!$request->isXmlHttpRequest()) {
      throw new BadRequestHttpException();
    }

    $json = file_get_contents('php://input');

    $params = json_decode($json);

    $breeds = $this->breederQueryService->getBreedsBySize($params->size);

    return new JsonResponse($breeds);
  }

  /**
   * @Route("/breeder/ajax/get_pref_by_breeds", name="breeder_ajax_get_pref_by_breeds")
   */
  public function breeder_ajax_get_pref_by_breeds(Request $request): JsonResponse
  {
    if (!$request->isXmlHttpRequest()) {
      throw new BadRequestHttpException();
    }

    $json = file_get_contents('php://input');

    $params = json_decode($json);

    $breeds = $this->breederQueryService->getPrefByBreeds($params->breeds, $params->pet_kind);

    return new JsonResponse($breeds);
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

    if($breeder->getIsActive() == 0){
      throw new NotFoundHttpException();
    }
    if($breeder->getExaminationStatus() != 2){
      throw new NotFoundHttpException();
    }


    $handling_pet_kind = $breeder->getHandlingPetKind();
    $dogHouse = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
    $catHouse = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

    $find_cond["Breeder"] = $breeder;
    $find_cond["is_active"] = 1;

    //販売中
    if ($request->query->get('filter_status') == 2) {
      $find_cond["is_delete"] = 0;
      $find_cond["dna_check_result"] = 1;
    }
    //検査中
    if ($request->query->get('filter_status') == 3) {
      $find_cond["dna_check_result"] = 0;
    }
    //成約済み
    if ($request->query->get('filter_status') == 4) {
      $find_cond["is_delete"] = 1;
      //$find_cond["is_contract"] = 1;
    }

    $petResults = $this->breederPetsRepository->findBy($find_cond, ["is_delete" => "ASC", "is_contract" => "ASC", "dna_check_result" => "DESC", "create_date" => "DESC"]);

    $pets = $paginator->paginate(
      $petResults,
      $request->query->getInt('page', 1),
      AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
    );

    $allEvaluations = $this->breederEvaluationsRepository->findBy(['Breeder' => $breeder, 'is_active' => 2], ['create_date' => 'DESC']);
    $evaluationCount = count($allEvaluations);
    $evaluations = $this->breederEvaluationsRepository->findBy(['Breeder' => $breeder, 'is_active' => 2], ['create_date' => 'DESC'], 3);

    $html_title = "「" . $breeder->getLicenseHouseName() . "」" . $breeder->getBreederName() . "ブリーダー";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_search'), 'title' => "ブリーダー検索"),
      array('url' => "#", 'title' => "「" . $breeder->getLicenseHouseName() . "」" . $breeder->getBreederName() . "ブリーダー")
    );

    $entityManager = $this->entityManager;
    $breeder->setViewCount(intval($breeder->getViewCount() + 1));
    $entityManager->persist($breeder);
    $entityManager->flush();

    return [
      'title' => $html_title,
      'breeder' => $breeder,
      'dogHouse' => $dogHouse,
      'catHouse' => $catHouse,
      'pets' => $pets,
      'evaluations' => $evaluations,
      'evaluationCount' => $evaluationCount,
      'maintitle' => $html_title,
      'breadcrumb' => $breadcrumb,
      "description_add" => $breeder->getBreederName() . "ブリーダー",
      "filter_status" => $request->query->get('filter_status')
    ];

    /*
        return compact(
            'breeder',
            'dogHouse',
            'catHouse',
            'pets',
            'evaluations',
            'evaluationCount'
        );
        */
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
   * よくあるご質問
   *
   * @Route("/breeder/faq", name="breeder_faq")
   * @Template("animalline/breeder/faq.twig")
   */
  public function faq(Request $request)
  {
    $maintitle = "よくあるご質問";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_faq'), 'title' => "よくあるご質問")
    );

    return [
      'title' => 'よくあるご質問',
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * 会社概要.
   *
   * @Route("/breeder/company", name="breeder_company")
   * @Template("animalline/breeder/company.twig")
   */
  public function company(Request $request)
  {
    $maintitle = "会社概要";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_company'), 'title' => "会社概要")
    );

    return [
      'title' => '会社概要',
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * ご購入の流れ.
   *
   * @Route("/breeder/buyinfo", name="breeder_buyinfo")
   * @Template("animalline/breeder/buyinfo.twig")
   */
  public function buyinfo(Request $request)
  {
    $maintitle = "ご購入の流れ";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_buyinfo'), 'title' => "ご購入の流れ")
    );

    return [
      'title' => 'ご購入の流れ',
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * お引渡しの流れ.
   *
   * @Route("/breeder/transferinfo", name="breeder_transferinfo")
   * @Template("animalline/breeder/transferinfo.twig")
   */
  public function transferinfo(Request $request)
  {
    $maintitle = "お引渡しの流れ";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_transferinfo'), 'title' => "お引渡しの流れ")
    );

    return [
      'title' => 'お引渡しの流れ',
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * お迎え時の費用について.
   *
   * @Route("/breeder/costinfo", name="breeder_costinfo")
   * @Template("animalline/breeder/costinfo.twig")
   */
  public function costinfo(Request $request)
  {
    $maintitle = "お迎え時の費用について";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_costinfo'), 'title' => "お迎え時の費用について")
    );

    return [
      'title' => 'お迎え時の費用について',
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * 初めての方へ.
   *
   * @Route("/breeder/firstinfo", name="breeder_firstinfo")
   * @Template("animalline/breeder/firstinfo.twig")
   */
  public function firstinfo(Request $request)
  {
    $maintitle = "初めての方へ";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_firstinfo'), 'title' => "初めての方へ")
    );

    return [
      'title' => '初めての方へ',
      'maintitle' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
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
    $maintitle = "個人情報保護方針";

    return [
      'title' => $maintitle,
    ];
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
    $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE);

    $form = $builder->getForm();
    $form->handleRequest($request);

    $newFilename = $request->get("newFilename");
    if ($form->isSubmitted()) {
      $brochureFile = $form->get('files')->getData();

      if ($brochureFile) {
        $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = 'contact-' . uniqid() . '.' . $brochureFile->guessExtension();

        $brochureFile->move(
          "var/tmp/contact/",
          $newFilename
        );

        $builder->setData(["files" => "var/tmp/contact/" . $newFilename]);
      }
    }

    if ($form->isSubmitted() && $form->isValid()) {
      switch ($request->get('mode')) {
        case 'confirm':
          $form = $builder->getForm();
          $form->handleRequest($request);

          return $this->render('animalline/breeder/ani_contact_confirm.twig', [
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
          $this->mailService->sendContactMail($data, $newFilename);

          // return $this->redirect($this->generateUrl('contact_complete'));
          return $this->render('animalline/breeder/ani_contact_complete.twig');
      }
    }
    $maintitle = "お問い合わせ";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_ani_contact'), 'title' => "お問い合わせ"),
    );

    return [
      'title' => $maintitle,
      'form' => $form->createView(),
      "newFilename" => $newFilename,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * DNA検査について
   *
   * @Route("/breeder/dnainfo", name="breeder_dnainfo")
   * @Template("animalline/breeder/dnainfo.twig")
   */
  public function dnainfo(Request $request)
  {
    $maintitle = "遺伝子検査";
    $breadcrumb = array(
      array('url' => $this->generateUrl('breeder_top'), 'title' => "犬・猫ブリーダー直販サイトのアニマルライン "),
      array('url' => $this->generateUrl('breeder_dnainfo'), 'title' => "遺伝子検査"),
    );

    return [
      'title' => $maintitle,
      'breadcrumb' => $breadcrumb,
    ];
  }

  /**
   * DNA検査項目
   *
   * @Route("/breeder/dnacheck", name="breeder_dnacheck")
   * @Template("animalline/breeder/dnacheck.twig")
   */
  public function dnacheck(Request $request)
  {
    $maintitle = "代表的な検査項目";

    return [
      'title' => $maintitle,
    ];
  }
}
