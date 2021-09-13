<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\BreederPetImage;
use Customize\Entity\BreederPets;
use Customize\Entity\BreederEvaluations;
use Customize\Form\Type\BreederEvaluationsType;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\BreedersType;
use Customize\Form\Type\BreederPetsType;
use Customize\Form\Type\BreederExaminationInfoType;
use Customize\Form\Type\Breeder\BreederHouseType;
use Customize\Entity\Breeders;
use Customize\Entity\BreederContacts;
use Customize\Entity\BreederContactHeader;
use Customize\Entity\BreederHouse;
use Customize\Entity\BreederExaminationInfo;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreederPetImageRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Breeder\BreederContactType;

class BreederMemberController extends AbstractController
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
     * @param BreederPetImageRepository $breederPetImageRepository
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
        BreederPetImageRepository     $breederPetImageRepository
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
    }

    /**
     *
     * マイページ
     *
     * @Route("/breeder/member/", name="breeder_mypage")
     * @Template("animalline/breeder/member/index.twig")
     */
    public function breeder_mypage(Request $request)
    {
        $user = $this->getUser();
        $breeder = $this->breedersRepository->find($user);
        /*
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
        */
        $pets = $this->breederQueryService->findBreederFavoritePets($this->getUser()->getId());

        return $this->render('animalline/breeder/member/index.twig', [
            //'rootMessages' => $rootMessages,
            //'lastReplies' => $lastReplies,
            'breeder' => $breeder,
            'pets' => $pets,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * 取引メッセージ一覧
     *
     * @Route("/breeder/member/all_message", name="breeder_all_message")
     * @Template("animalline/breeder/member/breeder_message.twig")
     */
    public function all_message(Request $request)
    {
        $listMessages = $this->breederContactHeaderRepository->findBy(['Customer' => $this->getUser()], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/breeder_message.twig', [
            'listMessages' => $listMessages
        ]);
    }

    /**
     * 取引メッセージ画面
     *
     * @Route("/breeder/member/message/{id}", name="breeder_message")
     * @Template("animalline/breeder/member/message.twig")
     */
    public function message(Request $request, BreederContactHeader $msgHeader)
    {
        $isScroll = false;
        $msgHeader->setCustomerNewMsg(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederHeader($msgHeader);

            $msgHeader->setBreederNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $isScroll = true;
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setBreederNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription('今回の取引非成立となりました')
                ->setSendDate(Carbon::now())
                ->setBreederHeader($msgHeader);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->persist($breederContact);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_all_message');
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->breederContactsRepository->findBy(['BreederHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_breeder_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return $this->render('animalline/breeder/member/message.twig', [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'breeder' => $msgHeader->getBreeder(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons,
            'isScroll' => $isScroll
        ]);
    }

    /**
     * 成約画面
     *
     * @Route("/breeder/member/contract/{pet_id}", name="breeder_contract", requirements={"pet_id" = "\d+"})
     * @Template("animalline/breeder/member/contract.twig")
     */
    public function contract(Request $request)
    {
        $pet_id = $request->get('pet_id');
        $pet = $this->breederPetsRepository->find($pet_id);
        if (!$pet) {
            throw new HttpException\NotFoundHttpException();
        }
        $msgHeader = $this->breederContactHeaderRepository->findOneBy([
            'Customer' => $this->getUser(),
            'Breeder' => $pet->getBreeder(),
            'Pet' => $pet
        ]);
        if (!$msgHeader) {
            throw new HttpException\NotFoundHttpException();
        }

        $petRate = $this->breederEvaluationsRepository->findOneBy(['Pet' => $pet]);
        if ($petRate) {
            return $this->redirectToRoute('breeder_all_message');
        }

        $contract = new BreederEvaluations();
        $builder = $this->formFactory->createBuilder(BreederEvaluationsType::class, $contract);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $thumbnail_path = $request->get('thumbnail_path') ?? '';

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render(
                        'animalline/breeder/member/contract_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'pet_id' => $pet_id,
                            'thumbnail_path' => $thumbnail_path
                        ]
                    );

                case 'complete':
                    $contract->setPet($pet)->setImagePath($thumbnail_path);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($contract);
                    $entityManager->flush();

                    return $this->redirectToRoute('breeder_contract_complete', ['pet_id' => $pet_id]);
            }
        }
        return [
            'form' => $form->createView(),
            'pet_id' => $pet_id,
            'thumbnail_path' => $thumbnail_path,
            'msg_id' => $msgHeader->getId()
        ];
    }

    /**
     * 成約完了画面
     *
     * @Route("/breeder/member/contract/complete/{pet_id}", name="breeder_contract_complete", requirements={"pet_id" = "\d+"})
     * @Template("animalline/breeder/member/contract_complete.twig")
     */
    public function contract_complete(Request $request)
    {
        $pet = $this->breederPetsRepository->find($request->get('pet_id'));
        if (!$pet) {
            throw new HttpException\NotFoundHttpException();
        }
        $breeder = $pet->getBreeder();
        $avgEvaluation = $this->breederQueryService->calculateBreederRank($breeder->getId());
        $breeder->setBreederRank($avgEvaluation);

        $msgHeader = $this->breederContactHeaderRepository->findOneBy([
            'Customer' => $this->getUser(),
            'Breeder' => $breeder,
            'Pet' => $pet
        ]);
        if (!$msgHeader) {
            throw new HttpException\NotFoundHttpException();
        }
        switch ($msgHeader->getContractStatus()) {
            case AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION:
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT);
                break;
            case AnilineConf::CONTRACT_STATUS_WAITCONTRACT:
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT);
                break;
        }
        $msgHeader->setCustomerCheck(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($breeder);
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        return $this->render('animalline/breeder/member/contract_complete.twig');
    }

    /**
     * ブリーダー登録申請画面
     *
     * @Route("/breeder/member/examination", name="breeder_examination")
     * @Template("animalline/breeder/member/examination.twig")
     */
    public function examination(Request $request)
    {
        $user = $this->getUser();
        $breeder = $this->breedersRepository->find($user);

        $step = 1;

        // 基本情報が登録済みであればSTEP2を表示
        if ($breeder) {
            $step = 2;

            // 基本情報の取扱ペットに対応する犬舎・猫舎情報が登録されていればSTEP3を表示
            $handling_pet_kind = $breeder->getHandlingPetKind();
            $dog_house_info = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
            $cat_house_info = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

            if ($handling_pet_kind == 0 && $cat_house_info && $dog_house_info) {
                $step = 3;
            }
            if ($handling_pet_kind == 1 && $dog_house_info) {
                $step = 3;
            }
            if ($handling_pet_kind == 2 && $cat_house_info) {
                $step = 3;
            }
            // 審査情報が登録されていればSTEP4を表示
            $dog_examination_info = $this->breederExaminationInfoRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
            $cat_examination_info = $this->breederExaminationInfoRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

            if ($handling_pet_kind == 0 && $dog_examination_info && $cat_examination_info) {
                $step = 4;
            }
            if ($handling_pet_kind == 1 && $dog_examination_info) {
                $step = 4;
            }
            if ($handling_pet_kind == 2 && $cat_examination_info) {
                $step = 4;
            }

            // 審査申請済であればSTEP5として審査中メッセージ
            $examination_status = $breeder->getExaminationStatus();
            if ($examination_status == 1) {
                $step = 5;
            }

            // 審査結果が出ていれば審査結果を表示
        }
        return $this->render('animalline/breeder/member/examination.twig', [
            'user' => $user,
            'breeder' => $breeder,
            'step' => $step,
        ]);
    }

    /**
     * 基本情報編集画面
     *
     * @Route("/breeder/member/baseinfo", name="breeder_baseinfo")
     * @Template("/animalline/breeder/member/base_info.twig")
     */
    public function base_info(Request $request, BreedersRepository $breedersRepository)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if($return_path == ""){
            $return_path = "breeder_examination";
        }
        
        $user = $this->getUser();

        $breederData = $breedersRepository->find($user);
        if (!$breederData) {
            $breederData = new Breeders;
            $breederData->setId($user->getId());
        }
        $builder = $this->formFactory->createBuilder(BreedersType::class, $breederData);

        $form = $builder->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnail_path = $request->get('thumbnail_path') ? $request->get('thumbnail_path') : $breederData->getThumbnailPath();

            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense())
                ->setThumbnailPath($thumbnail_path);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $entityManager->flush();
            return $this->redirectToRoute($return_path);
        } elseif (!$form->isSubmitted()) {

            // Customer情報から初期情報をセット
            $Customer = $this->customerRepository->find($user);
            $form->get('breeder_name')->setData($Customer->getname01() . $Customer->getname02());
            $form->get('breeder_kana')->setData($Customer->getkana01() . $Customer->getkana02());
            $form->get('breeder_zip')->setData($Customer->getPostalCode());
            $form->get('addr')->get('PrefBreeder')->setData($Customer->getPref());
            $form->get('addr')->get('breeder_city')->setData($Customer->getAddr01());
            $form->get('addr')->get('breeder_address')->setData($Customer->getAddr02());
            $form->get('breeder_tel')->setData($Customer->getPhoneNumber());
        }

        return [
            'return_path' => $return_path,
            'breederData' => $breederData,
            'form' => $form->createView()
        ];
    }

    /**
     * 犬舎・猫舎情報編集画面
     *
     * @Route("/breeder/member/house_info/{pet_type}", name="breeder_house_info")
     * @Template("/animalline/breeder/member/house_info.twig")
     */
    public function house_info(Request $request)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if($return_path == ""){
            $return_path = "breeder_examination";
        }

        $petType = $request->get('pet_type');
        $breeder = $this->breedersRepository->find($this->getUser());
        $breederHouse = $this->breederHouseRepository->findOneBy(['pet_type' => $petType, 'Breeder' => $breeder]);
        if (!$breederHouse) {
            $breederHouse = new BreederHouse();
        }
        $builder = $this->formFactory->createBuilder(BreederHouseType::class, $breederHouse);
        $breeder = $this->breedersRepository->find($this->getUser());

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $housePref = $breederHouse->getBreederHousePrefId();
            $breederHouse->setBreeder($breeder)
                ->setPetType($petType)
                ->setBreederHousePref($housePref['name']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederHouse);

            $entityManager->flush();

            return $this->redirectToRoute($return_path);
        }
        return [
            'return_path' => $return_path,
            'form' => $form->createView(),
            'petType' => $petType,
            'breeder' => $breeder,
        ];
    }

    /**
     * 審査情報編集画面
     *
     * @Route("/breeder/member/examination_info/{pet_type}", name="breeder_examination_info", methods={"GET","POST"})
     * @Template("/animalline/breeder/member/examination_info.twig")
     */
    public function examination_info(Request $request)
    {
        $petType = $request->get('pet_type');
        $breeder = $this->breedersRepository->find($this->getUser());
        $breederExaminationInfo = $this->breederExaminationInfoRepository->findOneBy([
            'Breeder' => $breeder,
            'pet_type' => $petType
        ]);
        $isEdit = false;
        if ($breederExaminationInfo) {
            $isEdit = true;
            if (in_array(
                $breederExaminationInfo->getPedigreeOrganization(),
                [AnilineConf::PEDIGREE_ORGANIZATION_JKC, AnilineConf::PEDIGREE_ORGANIZATION_KC]
            )) {
                $breederExaminationInfo->setGroupOrganization($breederExaminationInfo->getPedigreeOrganization());
                $breederExaminationInfo->setPedigreeOrganization(AnilineConf::PEDIGREE_ORGANIZATION_JKC);
            }
        } else {
            $breederExaminationInfo = new BreederExaminationInfo();
        }

        $form = $this->createForm(BreederExaminationInfoType::class, $breederExaminationInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $breederExaminationInfo->setPetType($petType)
                ->setBreeder($breeder);
            $formRequest = $request->request->get('breeder_examination_info');
            if ($formRequest['pedigree_organization'] == AnilineConf::PEDIGREE_ORGANIZATION_JKC) {
                $breederExaminationInfo->setPedigreeOrganization($formRequest['group_organization']);
            } else {
                $breederExaminationInfo->setPedigreeOrganization($formRequest['pedigree_organization']);
            }

            if ($formRequest['pedigree_organization'] != AnilineConf::PEDIGREE_ORGANIZATION_OTHER) {
                $breederExaminationInfo->setPedigreeOrganizationOther(null);
            }

            $breederExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_INPUT_COMPLETE);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederExaminationInfo);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_examination');
        }

        return $this->render('animalline/breeder/member/examination_info.twig', [
            'form' => $form->createView(),
            'isEdit' => $isEdit,
            'petType' => $petType == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫'
        ]);
    }

    /**
     * 審査結果提出
     *
     * @Route("/breeder/member/examination/submit", name="breeder_examination_submit")
     */
    public function examination_submit(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // ブリーダーの審査ステータスを変更
        $breeder = $this->breedersRepository->find($this->getUser());
        $breeder->setExaminationStatus(AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK);

        $entityManager->persist($breeder);

        // 犬舎・猫舎両方のパターンがあるため配列で取得
        $breederExaminationInfos = $this->breederExaminationInfoRepository->findBy([
            'Breeder' => $breeder,
        ]);

        // 審査情報のそれぞれの審査ステータスを変更
        foreach ($breederExaminationInfos as $breederExaminationInfo) {
            $breederExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_SUBMIT);
            $entityManager->persist($breederExaminationInfo);
        }

        $entityManager->flush();

        return $this->redirectToRoute('breeder_examination');
    }

    /**
     * お気に入り
     * 
     * @Route("/breeder/member/favorite", name="breeder_favorite")
     * @Template("animalline/breeder/favorite.twig")
     */
    public function favorite(PaginatorInterface $paginator, Request $request): ?Response
    {
        $favoritePetResults = $this->breederPetsRepository->findByFavoriteCount();
        $favoritePets = $paginator->paginate(
            $favoritePetResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/breeder/favorite.twig', ['pets' => $favoritePets]);
    }

    /**
     * @Route("/breeder/member/message/{contact_id}", name="breeder_mypage_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/breeder/member/message.twig")
     */
    public function breeder_message(Request $request)
    {
        $contactId = $request->get('contact_id');
        $rootMessage = $this->breederContactsRepository
            ->findOneBy(['id' => $contactId, 'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID]);
        if (!$rootMessage) {
            throw new HttpException\NotFoundHttpException();
        }

        $replyMessage = $request->get('reply_message');
        $isEnd = $request->get('end_negotiation');
        if ($replyMessage || $isEnd) {
            $breederContact = (new BreederContacts())
                ->setCustomer($this->getUser())
                ->setbreeder($rootMessage->getBreeder())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setPet($rootMessage->getPet())
                ->setContactType(AnilineConf::CONTACT_TYPE_REPLY)
                ->setContactDescription($replyMessage)
                ->setParentMessageId($rootMessage->getId())
                ->setSendDate(Carbon::now())
                ->setIsResponse(AnilineConf::RESPONSE_UNREPLIED)
                ->setContractStatus(AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION)
                ->setReason($isEnd ? $this->sendoffReasonRepository->find($request->get('reason')) : null);

            $rootMessage->setIsResponse(AnilineConf::RESPONSE_UNREPLIED);
            if ($isEnd) $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederContact);
            $entityManager->persist($rootMessage);
            $entityManager->flush();
        }

        $childMessages = $this->breederContactsRepository
            ->findBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository
            ->findBy(['is_breeder_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return $this->render('animalline/breeder/member/message.twig', [
            'rootMessage' => $rootMessage,
            'childMessages' => $childMessages,
            'reasons' => $reasons
        ]);
    }

    /**
     * @Route("/breeder/member/contact/{pet_id}", name="breeder_contact", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/breeder/contact.twig")
     */
    public function contact(Request $request)
    {
        $id = $request->get('pet_id');
        $pet = $this->breederPetsRepository->find($id);
        if (!$pet) {
            throw new HttpException\NotFoundHttpException();
        }

        $contact = new BreederContactHeader();
        $builder = $this->formFactory->createBuilder(BreederContactType::class, $contact);
        $event = new EventArgs(
            [
                'builder' => $builder,
                'contact' => $contact
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render(
                        'animalline/breeder/contact_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'id' => $id
                        ]
                    );

                case 'complete':
                    $contact
                        ->setSendDate(Carbon::now())
                        ->setPet($pet)
                        ->setBreeder($pet->getBreeder())
                        ->setCustomer($this->getUser())
                        ->setLastMessageDate(Carbon::now());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($contact);
                    $entityManager->flush();

                    return $this->redirectToRoute('breeder_contact_complete', ['pet_id' => $id]);
            }
        }

        return [
            'form' => $form->createView(),
            'id' => $id
        ];
    }

    /**
     * 取扱ペット一覧TOP
     *
     * @Route("/breeder/member/pet_list", name="breeder_pet_list")
     * @Template("animalline/breeder/member/pet_list.twig")
     */
    public function breeder_pet_list(Request $request)
     {
        $pets = $this->breederPetsRepository->findBy(['Breeder' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/breeder/member/pet_list.twig',
            [
                'breeder' => $this->getUser(),
                'pets' => $pets,
            ]
        );
    }

    /**
     * 
     * 新規ペット追加
     * 
     * @Route("/breeder/member/pets/new/{breeder_id}", name="breeder_mypage_pets_new", methods={"GET","POST"})
     */
    public
    function breeder_pets_new(Request $request, BreedersRepository $breedersRepository): Response
    {
        $user = $this->getUser();
        $is_breeder = $user->getIsBreeder();
        if( $is_breeder == 0 ){
            $breeder = $breedersRepository->find($request->get('breeder_id'));

            return $this->render('animalline/breeder/member/examination_guidance.twig', [
                'breeder' => $breeder
            ]);
        }
       
        $breederPet = new BreederPets();
        $form = $this->createForm(BreederPetsType::class, $breederPet,[
            'customer' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $breeder = $breedersRepository->find($request->get('breeder_id'));
            $breederPet->setBreeder($breeder);
            $breederPet->setDnaCheckResult(0);
            $breederPet->setReleaseStatus(1);
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
                ->setBreederPetId($breederPet);
            $petImage1 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setBreederPetId($breederPet);
            $petImage2 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setBreederPetId($breederPet);
            $petImage3 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setBreederPetId($breederPet);
            $petImage4 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setBreederPetId($breederPet);
            $breederPet
                ->addBreederPetImage($petImage0)
                ->addBreederPetImage($petImage1)
                ->addBreederPetImage($petImage2)
                ->addBreederPetImage($petImage3)
                ->addBreederPetImage($petImage4)
                ->setThumbnailPath($img0);

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($breederPet);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_pet_list');
        }

        return $this->render('animalline/breeder/member/pets/new.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * ペット情報編集
     * 
     * @Route("/breeder/member/pets/edit/{id}", name="breeder_mypage_pets_edit", methods={"GET","POST"})
     */
    public
    function breeder_pets_edit(Request $request, BreederPets $breederPet): Response
    {
        $form = $this->createForm(BreederPetsType::class, $breederPet);
        $breederPetImages = $this->breederPetImageRepository->findBy(
            ['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );
        $request->request->set('thumbnail_path', $breederPet->getThumbnailPath());
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $petId = $breederPet->getId();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);
            $entityManager = $this->getDoctrine()->getManager();
            $breederPet->setThumbnailPath($img0);
            $entityManager->persist($breederPet);
            foreach ($breederPetImages as $key => $image) {
                $image->setImageUri(${'img' . $key});
                $entityManager->persist($image);
            }
            $entityManager->flush();

            return $this->redirectToRoute('breeder_pet_list');
        }

        $petImages = [];
        foreach ($breederPetImages as $image) {
            $petImages[] = [
                'image_uri' => $image->getImageUri(),
                'sort_order' => $image->getSortOrder()
            ];
        }

        return $this->render('animalline/breeder/member/pets/edit.twig', [
            'breeder_pet' => $breederPet,
            'pet_mages' => $petImages,
            'form' => $form->createView()
        ]);
    }

    
    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $petId
     * @return string
     */
    private
    function setImageSrc($imageUrl, $petId)
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

}
