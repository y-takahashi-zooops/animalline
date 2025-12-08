<?php

namespace Customize\Controller\Breeder;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederPetImage;
use Customize\Entity\BreederPets;
use Customize\Entity\PetLike;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Breeder\BreederPetsType;
use Customize\Form\Type\Breeder\BreederPetMovieType;
use Customize\Entity\BreederHouse;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreederPetinfoTemplateRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Repository\DnaCheckStatusDetailRepository;
use Customize\Repository\DnaCheckKindsRepository;
use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Repository\PetLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class BreederPetController extends AbstractController
{
    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

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
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederHouse
     */
    protected $breederHouseRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusDetailRepository
     */
    protected $dnaCheckStatusDetailRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var BreederPetinfoTemplateRepository
     */
    protected $breederPetinfoTemplateRepository;

    /**
     * @var DnaCheckKindsRepository
     */
    protected $dnaCheckKindsRepository;
    
    /**
     * @var PetLikeRepository
     */
    protected $petLikeRepository;

    /**
     * BreederController constructor.
     *
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreedersRepository $breedersRepository
     * @param PrefRepository $prefRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param BreederEvaluationsRepository $breederEvaluationsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param BreederPetinfoTemplateRepository $breederPetinfoTemplateRepository
     * @param PetLikeRepository $petLikeRepository
     * @param DnaCheckStatusDetailRepository $dnaCheckStatusDetailRepository
     * @param DnaCheckKindsRepository $dnaCheckKindsRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        BreederContactsRepository        $breederContactsRepository,
        BreederQueryService              $breederQueryService,
        PetsFavoriteRepository           $petsFavoriteRepository,
        SendoffReasonRepository          $sendoffReasonRepository,
        BreedersRepository               $breedersRepository,
        PrefRepository                   $prefRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederPetsRepository            $breederPetsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        CustomerRepository               $customerRepository,
        BreederContactHeaderRepository   $breederContactHeaderRepository,
        BreederEvaluationsRepository     $breederEvaluationsRepository,
        BreederPetImageRepository        $breederPetImageRepository,
        DnaQueryService                  $dnaQueryService,
        DnaCheckStatusRepository         $dnaCheckStatusRepository,
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository,
        BreederPetinfoTemplateRepository $breederPetinfoTemplateRepository,
        PetLikeRepository $petLikeRepository,
        DnaCheckStatusDetailRepository $dnaCheckStatusDetailRepository,
        DnaCheckKindsRepository $dnaCheckKindsRepository,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    )
    {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breedersRepository = $breedersRepository;
        $this->prefRepository = $prefRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->customerRepository = $customerRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->breederPetinfoTemplateRepository = $breederPetinfoTemplateRepository;
        $this->petLikeRepository = $petLikeRepository;
        $this->dnaCheckStatusDetailRepository = $dnaCheckStatusDetailRepository;
        $this->dnaCheckKindsRepository = $dnaCheckKindsRepository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * 取扱ペット一覧TOP
     *
     * @Route("/breeder/member/pet_list", name="breeder_pet_list")
     * @Template("animalline/breeder/member/pet_list.twig")
     */
    public function breeder_pet_list(PaginatorInterface $paginator, Request $request): ?Response
    {
        $arrayPets = [];
        $pets = $this->breederQueryService->getListPet($this->getUser());
        foreach ($pets as $key => $pet) {
            $pet['check'] = false;
            if ($pet['bch_id']) {
                $msgHeader = $this->breederContactHeaderRepository->find($pet['bch_id']);
                $pet['message'] = $this->breederContactsRepository->findOneBy(['BreederContactHeader' => $msgHeader, 'message_from' => AnilineConf::MESSAGE_FROM_USER], ['create_date' => 'DESC']);
                if ($msgHeader->getBreederNewMsg() == AnilineConf::NEW_MESSAGE) {
                    $pet['check'] = true;
                }
                if ($pet['message']) {
                    if ($pet['message']->getIsReading() == AnilineConf::RESPONSE_UNREPLIED) {
                        $pet['check'] = true;
                    }
                }
            }
            $arrayPets[$pet['bp_id']] = $pet;
        }
        $arrayPets = $paginator->paginate(
            $arrayPets,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render(
            'animalline/breeder/member/pet_list.twig',
            [
                'breeder' => $this->getUser(),
                'pets' => $arrayPets,
            ]
        );
    }

    /**
     * 動画投稿
     *
     * @Route("/breeder/member/movie_upload/{pet_id}", name="movie_upload", requirements={"pet_id" = "\d+"})
     * @Template("animalline/breeder/member/movie_upload.twig")
     */
    public function movie_upload(Request $request,$pet_id)
    {
        $entityManager = $this->entityManager;

        $pet = $this->breederPetsRepository->find($pet_id);
        if (!$pet) {
            throw new HttpException\NotFoundHttpException();
        }

        $builder = $this->formFactory->createBuilder(BreederPetMovieType::class);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $thumbnail_path = $request->get('thumbnail_path') ?? '';

        $is_upload_error = false;
        if ($form->isSubmitted() && !$form->isValid()) {
            $is_upload_error = true;
        }

        if ($form->isSubmitted() && $form->isValid()) {

            //受信ファイル処理
            $brochureFile = $form->get('movie_file')->getData();

            if($brochureFile){
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = 'pmovie-'.uniqid().'.'.$brochureFile->guessExtension();

                $brochureFile->move(
                    "html/upload/movie/",
                    $newFilename
                );

                $pet->setMovieFile($newFilename);
                $entityManager->persist($pet);
                $entityManager->flush();
            }
        }

        return [
            'pet' => $pet,
            'form' => $form->createView(),
            'is_upload_error' => $is_upload_error
        ];
    }

    /**
     * ペット詳細
     *
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request, PaginatorInterface $paginator): ?Response
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $breederPet = $this->breederPetsRepository->find($id);
        $pedigree = $breederPet->getPedigree();
        $breederExamInfo = null;

        $isPedigree = $breederPet->getIsPedigree();
        if ($isPedigree == 1) {
            $breeder = $this->breedersRepository->find($breederPet->getBreeder());
            $breederExamInfo = $this->breederExaminationInfoRepository->findOneBy([
                'Breeder' => $breeder->getId(),
                'pet_type' => $breederPet->getPetKind(),
                'pedigree_organization' => 3
            ]);
            if (!$breederExamInfo) {
                $breederExamInfo = $this->breederExaminationInfoRepository->findOneBy([
                    'Breeder' => $breeder->getId(),
                    'pet_type' => $breederPet->getPetKind(),
                    'pedigree_organization' => [
                        1,
                        2
                    ]
                ]);
            }
        }

        $petKind = $breederPet->getPetKind();
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
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

        $now = Carbon::now();
        $is56DaysOld = -1;
        if ($now > $breederPet->getPetBirthday()) {
            $is56DaysOld = $now->diffInDays($breederPet->getPetBirthday());
        }

        $html_title = "【ペットID : ".$breederPet->getId()."】".$breederPet->getBreedsType()->getbreedsName() . "（".$breederPet->getPetBirthday()->format("Y年m月d日")."　".$breederPet->getCoatColor()."）";

        //$maintitle = "犬・猫ブリーダー直販のアニマルライン";
        $breadcrumb = array(
            array('url' => $this->generateUrl('breeder_top'),'title' =>"犬・猫ブリーダー直販サイトのアニマルライン "),
            array('url' => "https://animalline.jp/breeder/pet/search/result?breed_type=" . $breederPet->getBreedsType()->getId(),'title' => $breederPet->getBreedsType()->getbreedsName()),
            array('url' => "#",'title' => "ペットID : ".$breederPet->getId())
        );

        $sql_state = "UPDATE alm_breeder_pets SET view_count = :viewcnt WHERE id = :petid";
        $view_cnt = intval($breederPet->getViewCount() + 1);
        $pet_id = $breederPet->getId();
        $stmt = $this->entityManager->getConnection()->prepare($sql_state);
        $stmt->bindParam('viewcnt', $view_cnt);
        $stmt->bindParam('petid', $pet_id);
        $stmt->execute();

        //$breederPet->setViewCount(intval($breederPet->getViewCount() + 1));
        //$entityManager->persist($breederPet);
        //$entityManager->flush();

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

        //ブリーダーのペット
        $find_cond["Breeder"] = $breederPet->getBreeder();
        $find_cond["is_active"] = 1;

        $petResults = $this->breederPetsRepository->findBy($find_cond, ["is_delete" => "ASC", "is_contract" => "ASC", "dna_check_result" => "DESC", "create_date" => "DESC"]);

        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        //いいね
        $sessid = session_id();
        $like = $this->petLikeRepository->getLike($sessid,1,$id);
        $like_count = $this->petLikeRepository->getLikeCount(1,$id);

        //DNA検査結果
        //$dna_check_results = array(0 => "－", 1 => "－", 2 => "－");
        $dna_check_kinds = array();
        $dna_check_header = $this->dnaCheckStatusRepository->findOneBy(["pet_id" => $id,"check_status" => 6]);
        $dna_check_result = "";

        if($dna_check_header){
            $dna_check_details = $this->dnaCheckStatusDetailRepository->findBy(["CheckStatus" => $dna_check_header]);

            //1:クリア 2:キャリア:劣勢 3:アフェクテッド 4:キャリア:優勢
            $dna_check_result = "◯（クリア）";
            foreach($dna_check_details as $dna_check_detail){
                $dna_kind = $dna_check_detail->getCheckKinds();
                $dna_check_kinds[] = $dna_kind->getCheckKind();

                switch($dna_check_detail->getCheckResult()){
                    case 1 :
                        break;
                    case 2:
                        $dna_check_result = "◯（劣性キャリア）";
                        break;
                    case 3:
                        $dna_check_result = "✕（アフェクテッド）";
                        break;
                    case 4:
                        $dna_check_result = "△（優性キャリア）";
                        break;
                }
            }
        }
        else{
            $dna_check_result = "検査中";

            $dna_check_kinds = [];
            foreach ($this->dnaCheckKindsRepository->findBy(
                ['Breeds' => $breederPet->getBreedsType(), 'delete_flg' => 0],
                ['update_date' => 'DESC', 'id' => 'DESC']
            ) as $item) {
                $dna_check_kinds[] = $item->getCheckKind();
            }
        }
        

        return $this->render(
            'animalline/breeder/pet/detail.twig',
            [
                'title' => $html_title,
                'breederPet' => $breederPet,
                'petKind' => $petKind,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn,
                'breederExamInfo' => $breederExamInfo,
                'pedigree' => $pedigree,
                'is56DaysOld' => $is56DaysOld,
                'maintitle' => $html_title,
                'breadcrumb' => $breadcrumb,
                "description_add" => "【ペットID : ".$breederPet->getId()."】".$breederPet->getBreedsType()->getbreedsName() . "（".$breederPet->getPetBirthday()->format("Y年m月d日")."　".$breederPet->getCoatColor()."）",
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
                'pets' => $pets,
                'like' => $like,
                'like_count' => $like_count,
                'dna_check_result' => $dna_check_result,
                'dna_check_kinds' => $dna_check_kinds,
            ]
        );
    }

    /**
     * いいね
     * @Route("/breeder/pet/like/{id}", name="breeder_pet_like", requirements={"id" = "\d+"})
     */
    public function petLike(Request $request, $id)
    {
        $sessid = session_id();

        $entityManager = $this->entityManager;

        $like =  $this->petLikeRepository->getLike($sessid, 1, $id);
        if(!$like){
            $like = new PetLike();
        }
        $like->setSessionId($sessid);
        $like->setSiteType(1);
        $like->setPetId($id);
        $entityManager->persist($like);
        $entityManager->flush();

        return $this->redirectToRoute('breeder_pet_detail', ['id' => $id]);
    }
    
    /**
     * 新規ペット追加
     *
     * @Route("/breeder/member/pets/new/{kind}/{breeder_id}", name="breeder_pets_new", methods={"GET","POST"})
     */
    public function breeder_pets_new(Request $request, $kind = 0,$breeder_id = ""): Response
    {
        // $barcode = "";
        $barcode = $request->barcode;

        if($breeder_id != ""){
            //breeder_id指定がある場合はログインユーザーチェックを行い、許可ユーザーであれば指定のブリーダーをシミュレート
            $user = $this->getUser();
            if($user->getId() == 91 || $user->getId() == 236){
                $user = $this->customerRepository->find($breeder_id);

                if(!$user){
                    throw new NotFoundHttpException();
                }
            }
            else{
                throw new NotFoundHttpException();
            }
        }
        else{
            //breeder_id指定がない場合はログイン中ユーザーとして処理
            $user = $this->getUser();
        }

        /*
        $dnaId = substr($barcode, 1);
        if (!$Dna = $this->dnaCheckStatusRepository->find($dnaId)) {
            throw new NotFoundHttpException();
        }
        $breederId = $Dna->getDnaHeader()->getRegisterId();
        */
        $breeder = $this->breedersRepository->find($user);
        $breederId = $breeder->getId();

        //ブリーダー審査通過チェック
        $is_breeder = $user->getIsBreeder();
        if ($is_breeder == 0) {
            return $this->render('animalline/breeder/member/examination_guidance.twig', [
                'breeder' => $breeder
            ]);
        }

        if($barcode != ""){
            $dnaId = substr($barcode, 1);
            if (!$Dna = $this->dnaCheckStatusRepository->find($dnaId)) {
                throw new NotFoundHttpException();
            }
            //ペット登録済チェック
            if(!is_null($Dna->getPetId())){
                return $this->render('animalline/breeder/member/already_regist.twig', [
                    'breeder' => $breeder
                ]);
            }
        }
        //ペット登録済チェック
        /*
        if(!is_null($Dna->getPetId())){
            return $this->render('animalline/breeder/member/already_regist.twig', [
                'breeder' => $breeder
            ]);
        }
        */

        //犬・猫いずれかのみ取扱の場合は種別選択なし
        $handling = $breeder->getHandlingPetKind();
        if($kind == 0){
            $kind = $handling;
        }

        //犬・猫両方取扱の場合は選択ページを表示
        if($kind == 0){
            return $this->render('animalline/breeder/member/kind_select.twig', [
                'breeder' => $breeder,
                'barcode' => $barcode,
                'breeder_id' => $breeder_id
            ]);
        }

        if (!$breeder) {
            throw new NotFoundHttpException();
        }
        $petInfoTemplate = $this->breederPetinfoTemplateRepository->findOneBy([
            'Breeder' => $breeder
        ]);

        $image0 = $request->get('img0') ?? '';
        $image1 = $request->get('img1') ?? '';
        $image2 = $request->get('img2') ?? '';
        $image3 = $request->get('img3') ?? '';
        $image4 = $request->get('img4') ?? '';

        // if (!$petInfoTemplate) {
        //     throw new NotFoundHttpException();
        // }
        $breederPet = new BreederPets();
        $form = $this->createForm(BreederPetsType::class, $breederPet, [
            'customer' => $user,
            'pet_kind' => $kind,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
            if ($request->get('breeder_pets')['is_pedigree'] == 0 || $request->get('breeder_pets')['pedigree_code']) {
                $breederPet->setPedigreeCode('0');
            }
            */
            $entityManager = $this->entityManager;
            $breeder = $this->breedersRepository->find($breederId);
            $breederPet->setBreeder($breeder);
            $breederPet->setIsActive(1);
            $breederPet->setIsContact(0);
            $breederPet->setDnaCheckResult(0);
            $breederPet->setViewCount(0);
            $entityManager->persist($breederPet);
            $entityManager->flush();
            $petId = $breederPet->getId();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);

            $petImage0 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img0)->setSortOrder(1)
                ->setBreederPet($breederPet);
            $petImage1 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setBreederPet($breederPet);
            $petImage2 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setBreederPet($breederPet);
            $petImage3 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setBreederPet($breederPet);
            $petImage4 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setBreederPet($breederPet);
            $breederPet
                ->addBreederPetImage($petImage0)
                ->addBreederPetImage($petImage1)
                ->addBreederPetImage($petImage2)
                ->addBreederPetImage($petImage3)
                ->addBreederPetImage($petImage4)
                ->setThumbnailPath($img0)
                ->setPetCode($barcode);

            if($barcode != ""){
                // update dna check status
                $Dna->setPetId($breederPet->getId())
                    ->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED)
                    ->setKitPetRegisterDate(new DateTime);
                $entityManager->persist($Dna);
            }
            // update dna check status
            /*
            $Dna->setPetId($breederPet->getId())
                ->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED)
                ->setKitPetRegisterDate(new DateTime);
            */

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($breederPet);
            // $entityManager->persist($Dna);
            $entityManager->flush();

            if($breeder_id != ""){
                return $this->redirectToRoute('close_window');
            }
            else{
                return $this->redirectToRoute('breeder_newpet_complete');
            }
            //return $this->render('animalline/breeder/member/pets/notification.twig');
        }

        $is_error = false;
        if ($form->isSubmitted() && !$form->isValid()) {
            $is_error = true;
        }

        return $this->render('animalline/breeder/member/pets/new.twig', [
            'is_error' => $is_error,
            'form' => $form->createView(),
            'petInfoTemplate' => $petInfoTemplate,
            'image0' => $image0,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4,
            'isCheckPetContract' => false,
            'barcode' => $barcode
        ]);
    }

    /**
     *
     * 新規ペット追加完了メッセージ
     *
     * @Route("/breeder/member/pets/new_complete", name="breeder_newpet_complete", methods={"GET","POST"})
     * @Template("animalline/breeder/member/pets/notification.twig")
     */
    public function breeder_pets_new_complete(): array
    {
        return [];
    }

    /**
     * ペット情報編集
     *
     * @Route("/breeder/member/pets/edit/{id}/{breeder_id}", name="breeder_pets_edit", methods={"GET","POST"})
     * @Template("animalline/breeder/member/pets/edit.twig")
     */
    public function breeder_pets_edit(Request $request, BreederPets $breederPet,string $breeder_id = "")
    {
        $isCheckPetContract = !is_null($this->breederContactHeaderRepository->findOneBy(['Pet' => $breederPet, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT]));

        if($breeder_id != ""){
            //breeder_id指定がある場合はログインユーザーチェックを行い、許可ユーザーであれば指定のブリーダーをシミュレート
            $user = $this->getUser();
            if($user->getId() == 91 || $user->getId() == 236){
                $user = $this->customerRepository->find($breeder_id);

                if(!$user){
                    throw new NotFoundHttpException();
                }
            }
            else{
                throw new NotFoundHttpException();
            }
        }
        else{
            //breeder_id指定がない場合はログイン中ユーザーとして処理
            $user = $this->getUser();
        }

        $breeder = $this->breedersRepository->find($user);
        if (!$breeder) {
            throw new NotFoundHttpException();
        }
        $petInfoTemplate = $this->breederPetinfoTemplateRepository->findOneBy([
            'Breeder' => $breeder
        ]);

        /*
        if (!$petInfoTemplate) {
            throw new NotFoundHttpException();
        }
        */

        $image0 = $request->get('img0') ?? '';
        $image1 = $request->get('img1') ?? '';
        $image2 = $request->get('img2') ?? '';
        $image3 = $request->get('img3') ?? '';
        $image4 = $request->get('img4') ?? '';

        $form = $this->createForm(BreederPetsType::class, $breederPet, [
            'customer' => $user
        ]);
        $breederPetImages = $this->breederPetImageRepository->findBy(
            ['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );

        // $request->request->set('thumbnail_path', $breederPet->getThumbnailPath() ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $breederPet->getThumbnailPath() : '');
        $thumbnailPath = $breederPet->getThumbnailPath()
            ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $breederPet->getThumbnailPath()
            : '';
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $request->request->set('thumbnail_path', $image0);
            /*
            if ($request->get('breeder_pets')['is_pedigree'] == 0 || $request->get('breeder_pets')['pedigree_code']) {
                $breederPet->setPedigreeCode('0');
            }
            */

            if ($form->isValid()) {
                $petId = $breederPet->getId();
                $img0 = $this->setImageSrc($request->get('img0'), $petId);
                $img1 = $this->setImageSrc($request->get('img1'), $petId);
                $img2 = $this->setImageSrc($request->get('img2'), $petId);
                $img3 = $this->setImageSrc($request->get('img3'), $petId);
                $img4 = $this->setImageSrc($request->get('img4'), $petId);
                $entityManager = $this->entityManager;
                $breederPet->setThumbnailPath($img0);
                $entityManager->persist($breederPet);
                foreach ($breederPetImages as $key => $image) {
                    $image->setImageUri(${'img' . $key});
                    $entityManager->persist($image);
                }
                $entityManager->flush();

                if($breeder_id != ""){
                    //管理画面からの場合はWindowを閉じる
                    return $this->redirectToRoute('close_window');
                }
                else{
                    return $this->redirectToRoute('breeder_pet_list');
                }
                
            }
        }
        $petImages = [];
        foreach ($breederPetImages as $key => $image) {
            if ($form->isSubmitted()) {
                $petImages[$key] = [
                    'image_uri' => $request->get('img' . $key),
                    'sort_order' => $image->getSortOrder()
                ];
            } else {
                $petImages[$key] = [
                    'image_uri' => $image->getImageUri() ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $image->getImageUri() : '',
                    'sort_order' => $image->getSortOrder()
                ];
            }
        }

        $is_error = false;
        if ($form->isSubmitted() && !$form->isValid()) {
            $is_error = true;
        }

        return [
            'is_error' => $is_error,
            'breeder_pet' => $breederPet,
            'breeder' => $breederPet->getBreeder(),
            'pet_mages' => $petImages,
            'thumbnailPath' => $thumbnailPath,
            'form' => $form->createView(),
            'petInfoTemplate' => $petInfoTemplate,
            'image0' => $image0,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4,
            'isCheckPetContract' => $isCheckPetContract
        ];
    }

    /**
     * ペットの状態を変更する
     *
     * @Route("/breeder/member/pets/edit/{id}/change_status/execute", name="breeder_pets_edit_change_status", methods={"GET"})
     */
    public function breeder_pets_change_status(Request $request, BreederPets $breederPet)
    {
        $curStatus = $breederPet->getIsActive();
        if ($curStatus == 1) {
            $breederPet->setIsActive(2);
            $breederPet->setReleaseDate(Carbon::now());
        } else {
            $breederPet->setIsActive(1);
            $breederPet->setReleaseDate(null);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($breederPet);
        $em->flush();

        return $this->redirectToRoute('breeder_pets_edit', ['id' => $breederPet->getId()]);
    }

    /**
     * ペットの状態を変更する
     *
     * @Route("/breeder/member/pets/edit/{id}/change_contact/execute", name="breeder_pets_edit_contact_status", methods={"GET"})
     */
    public function breeder_pets_contact_status(Request $request, BreederPets $breederPet)
    {
        $curStatus = $breederPet->getIsContact();
        if ($curStatus == 1) {
            $breederPet->setIsContact(0);
        } else {
            $breederPet->setIsContact(1);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($breederPet);
        $em->flush();

        return $this->redirectToRoute('breeder_pets_edit', ['id' => $breederPet->getId()]);
    }

    /**
     * ペットの状態を削除する
     *
     * @Route("/breeder/member/pets/edit/{id}/delete/execute", name="breeder_pets_delete", methods={"GET"})
     */
    public function breeder_pets_delete(BreederPets $breederPet)
    {
        $breederPet->setIsDelete(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($breederPet);
        $em->flush();

        return $this->redirectToRoute('breeder_pet_list');
    }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $petId
     * @return string
     */
    private function setImageSrc(string $imageUrl, int $petId): string
    {
        if (empty($imageUrl)) {
            return '';
        }

        $imageUrl = ltrim($imageUrl, '/');
        $resource = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE,
            '',
            $imageUrl
        );
        $arr = explode('/', ltrim($resource, '/'));
        if ($arr[0] === 'breeder') {
            return $resource;
        }

        $imageName = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/',
            '',
            $imageUrl
        );
        $subUrl = AnilineConf::ANILINE_IMAGE_URL_BASE . '/breeder/' . $petId . '/';
        if (!file_exists($subUrl)) {
            mkdir($subUrl, 0777, 'R');
        }

        copy($imageUrl, $subUrl . $imageName);
        return '/breeder/' . $petId . '/' . $imageName;
    }

    /**
     *
     * ペット登録一覧
     *
     * @Route("/breeder/member/pet_regist_list", name="breeder_pet_regist_list")
     * @Template("animalline/breeder/member/pets/regist_list.twig")
     */
    public function pet_regist_list(Request $request, PaginatorInterface $paginator): array
    {
        $codes = [];
        $dnaCheckStatusHeaders = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $this->getUser()->getId()]);
        $DnaCheckStatus = $this->dnaCheckStatusRepository->createQueryBuilder('dcs')
            ->where('dcs.DnaHeader IN(:arr)')
            ->andWhere('dcs.site_type = :siteType')
            ->andWhere('dcs.check_status = :checkStatus')
            ->setParameter('siteType', AnilineConf::ANILINE_SITE_TYPE_BREEDER)
            ->setParameter('checkStatus', AnilineConf::ANILINE_DNA_CHECK_STATUS_SHIPPING)
            ->setParameter('arr', $dnaCheckStatusHeaders)
            ->addOrderBy('dcs.update_date', 'DESC')
            ->getQuery()->getResult();
        foreach ($DnaCheckStatus as $dnaCheckStatus) {
            $codes[] = '1' . str_pad($dnaCheckStatus->getId(), 5, '0', STR_PAD_LEFT);
        }
        $barCodes = $paginator->paginate(
            $codes,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return compact('barCodes');
    }

    /**
     *
     * @Route("/b", name="b")
     */
    public function b()
    {
        return $this->redirectToRoute('breeder_pet_regist_list');
    }
}
