<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPetImage;
use Customize\Entity\ConservationPets;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\AdoptionQueryService;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\ConservationsType;
use Customize\Form\Type\ConservationPetsType;
use Customize\Form\Type\ConservationHouseType;
use Customize\Entity\Conservations;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationsHouse;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\ConservationPetImageRepository;

use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\ConservationContactType;
use DateTime;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;

class AdoptionMemberController extends AbstractController
{
    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

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
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHouseRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * ConservationController constructor.
     *
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param ConservationsRepository $conservationsRepository
     * @param PrefRepository $prefRepository
     * @param ConservationsHousesRepository $conservationsHouseRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param CustomerRepository $customerRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaQueryService $dnaQueryService
     */
    public function __construct(
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        ConservationContactsRepository      $conservationContactsRepository,
        AdoptionQueryService                $adoptionQueryService,
        PetsFavoriteRepository              $petsFavoriteRepository,
        SendoffReasonRepository             $sendoffReasonRepository,
        ConservationsRepository             $conservationsRepository,
        PrefRepository                      $prefRepository,
        ConservationsHousesRepository       $conservationsHouseRepository,
        ConservationPetsRepository          $conservationPetsRepository,
        CustomerRepository                  $customerRepository,
        ConservationPetImageRepository      $conservationPetImageRepository,
        DnaCheckStatusRepository            $dnaCheckStatusRepository,
        DnaQueryService                     $dnaQueryService
    )
    {
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->prefRepository = $prefRepository;
        $this->conservationsHouseRepository = $conservationsHouseRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->customerRepository = $customerRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaQueryService = $dnaQueryService;
    }

    /**
     * ログイン画面.
     *
     * @Route("/adoption/login", name="adoption_login")
     * @Template("animalline/adoption/login.twig")
     */
    public function adoption_login(Request $request, AuthenticationUtils $utils)
    {
        //ログイン完了後に元のページに戻るためのセッション変数を設定
        $referer = $request->headers->get('referer');
        /*
        if($referer){
            $referers = parse_url($referer);
            if($referers['host'] == $request->getHost()) {
                $this->setLoginTargetPath($referer);
            }
        }
        */
        //ログイン完了後に元のページに戻るためのセッション変数を設定

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('adoption_mypage');
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory
            ->createNamedBuilder('', CustomerLoginType::class);

        $builder->get('login_memory')->setData((bool)$request->getSession()->get('_security.login_memory'));

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Customer = $this->getUser();
            if ($Customer instanceof Customer) {
                $builder->get('login_email')
                    ->setData($Customer->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_LOGIN_INITIALIZE, $event);

        $form = $builder->getForm();

        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
    }

    /**
     *
     * マイページ
     *
     * @Route("/adoption/member/", name="adoption_mypage")
     * @Template("animalline/adoption/member/index.twig")
     */
    public function adoption_mypage(Request $request)
    {
        $user = $this->getUser();
        $conservation = $this->conservationsRepository->find($user);

        $pets = $this->adoptionQueryService->findAdoptionFavoritePets($this->getUser()->getId());

        return $this->render('animalline/adoption/member/index.twig', [
            'conservation' => $conservation,
            'pets' => $pets,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * 取引メッセージ一覧
     *
     * @Route("/adoption/member/all_message", name="adoption_all_message")
     * @Template("animalline/adoption/member/adoption_message.twig")
     */
    public function all_message()
    {
        $listMessages = $this->conservationContactHeaderRepository->findBy(['Customer' => $this->getUser()], ['last_message_date' => 'DESC']);

        return $this->render('animalline/adoption/member/adoption_message.twig', [
            'listMessages' => $listMessages
        ]);
    }


    /**
     * 取引メッセージ画面
     *
     * @Route("/adoption/member/message/{id}", name="adoption_message", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/member/message.twig")
     */
    public function adoption_message(Request $request, ConservationContactHeader $rootMessage)
    {
        $isScroll = false;
        if ($request->isMethod('POST')) {
            $replyMessage = $request->get('reply_message');
            $now = new DateTime();

            $conservationContact = (new ConservationContacts())
                ->setConservationHeader($rootMessage)
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate($now);

            $rootMessage->setConservationNewMsg(1);
            $rootMessage->setLastMessageDate($now);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationContact);
            $entityManager->persist($rootMessage);
            $entityManager->flush();

            $isScroll = true;
        } else if ($rootMessage->getCustomerNewMsg()) {
            $rootMessage->setCustomerNewMsg(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rootMessage);
            $entityManager->flush();
        }

        $childMessages = $this->conservationContactsRepository->findBy(['ConservationHeader' => $rootMessage], ['send_date' => 'ASC']);
        $pet = $rootMessage->getPet();
        $conservation = $rootMessage->getConservation();
        $reasons = $this->sendoffReasonRepository->findBy(['is_adoption_visible' => AnilineConf::ADOPTION_VISIBLE_SHOW]);

        return compact(
            'rootMessage',
            'childMessages',
            'pet',
            'conservation',
            'reasons',
            'isScroll'
        );
    }

    /**
     * 保護団体登録申請画面
     *
     * @Route("/adoption/member/examination", name="adoption_examination")
     * @Template("animalline/adoption/member/examination.twig")
     */
    public function examination(Request $request)
    {
        $user = $this->getUser();
        $conservation = $this->conservationsRepository->find($user);

        $step = 1;

        // 基本情報が登録済みであればSTEP2を表示
        if ($conservation) {
            $step = 2;

            // 基本情報の取扱ペットに対応する犬舎・猫舎情報が登録されていればSTEP3を表示
            $handling_pet_kind = $conservation->getHandlingPetKind();
            $dog_house_info = $this->conservationsHouseRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 1]);
            $cat_house_info = $this->conservationsHouseRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 2]);

            // if($handling_pet_kind == 0 && $cat_house_info && $dog_house_info){$step = 3;}
            // if($handling_pet_kind == 1 && $dog_house_info){$step = 3;}
            // if($handling_pet_kind == 2 && $cat_house_info){$step = 3;}

            // 審査情報が登録されていればSTEP4を表示
            // $dog_examination_info = $this->conservationExaminationInfoRepository->findOneBy(["Conservation" => $conservation,"pet_type" => 1]);
            // $cat_examination_info = $this->conservationExaminationInfoRepository->findOneBy(["Conservation" => $conservation,"pet_type" => 2]);

            // if($handling_pet_kind == 0 && $dog_examination_info && $cat_examination_info ){$step = 4;}
            // if($handling_pet_kind == 1 && $dog_examination_info ){$step = 4;}
            // if($handling_pet_kind == 2 && $cat_examination_info ){$step = 4;}

            // 審査情報は未実装なのでSTEP3の条件を満たしていればSTEP4を表示
            if ($handling_pet_kind == 0 && $cat_house_info && $dog_house_info) {
                $step = 4;
            }
            if ($handling_pet_kind == 1 && $dog_house_info) {
                $step = 4;
            }
            if ($handling_pet_kind == 2 && $cat_house_info) {
                $step = 4;
            }

            // 審査申請済であればSTEP5として審査中メッセージ
            $examination_status = $conservation->getExaminationStatus();
            if ($examination_status == 1) {
                $step = 5;
            }

            // 審査結果が出ていれば審査結果を表示
        }
        return $this->render('animalline/adoption/member/examination.twig', [
            'user' => $user,
            'conservation' => $conservation,
            'step' => $step,
        ]);
    }

    /**
     * 基本情報編集画面
     *
     * @Route("/adoption/member/baseinfo", name="adoption_baseinfo")
     * @Template("/animalline/adoption/member/base_info.twig")
     */
    public function base_info(Request $request, ConservationsRepository $conservationsRepository, PrefRepository $prefRepository)
    {
        $user = $this->getUser();

        $conservation = $conservationsRepository->find($user);
        if (!$conservation) {
            $conservation = new Conservations;
            $conservation->setId($user->getId());
        }
        $builder = $this->formFactory->createBuilder(ConservationsType::class, $conservation);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addr = $request->get('conservations')['addr'];
            $pref = $prefRepository->find($addr['PrefId']);
            $thumbnail_path = $request->get('thumbnail_path') ?: $conservation->getThumbnailPath();

            $conservation->setPrefId($pref)
                ->setPref($pref->getName())
                ->setId($user->getId())
                ->setCity($addr['city'])
                ->setAddress($addr['address'])
                ->setThumbnailPath($thumbnail_path);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();
            return $this->redirectToRoute('adoption_examination');
        } elseif (!$form->isSubmitted()) {

            // Customer情報から初期情報をセット
            $Customer = $this->customerRepository->find($user);
            $form->get('owner_name')->setData($Customer->getname01() . "　" . $Customer->getname02());
            $form->get('owner_kana')->setData($Customer->getkana01() . "　" . $Customer->getkana02());
            $form->get('zip')->setData($Customer->getPostalCode());
            $form->get('addr')->get('PrefId')->setData($Customer->getPref());
            $form->get('addr')->get('city')->setData($Customer->getAddr01());
            $form->get('addr')->get('address')->setData($Customer->getAddr02());
            $form->get('tel')->setData($Customer->getPhoneNumber());
        }

        return [
            'conservation' => $conservation,
            'form' => $form->createView()
        ];
    }

    /**
     * 犬舎・猫舎情報編集画面
     *
     * @Route("/adoption/member/house_info/{pet_type}", name="adoption_house_info")
     * @Template("/animalline/adoption/member/house_info.twig")
     */
    public function house_info(Request $request)
    {
        $petType = $request->get('pet_type');
        $conservation = $this->conservationsRepository->find($this->getUser());
        $conservationsHouse = $this->conservationsHouseRepository->findOneBy(['pet_type' => $petType, 'Conservation' => $conservation]);
        if (!$conservationsHouse) {
            $conservationsHouse = new ConservationsHouse();
        }
        $builder = $this->formFactory->createBuilder(ConservationHouseType::class, $conservationsHouse);
        $conservation = $this->conservationsRepository->find($this->getUser());

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $address = $request->get('conservation_house')['address'];
            $conservationsHouse->setConservation($conservation)
                ->setPetType($petType)
                ->setConservationHousePref($conservationsHouse->getPref()->getName())
                ->setConservationHouseCity($address['conservation_house_city'])
                ->setConservationHouseAddress($address['conservation_house_address']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationsHouse);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_examination');
        }
        return [
            'form' => $form->createView(),
            'petType' => $petType,
            'conservation' => $conservation,
        ];
    }

    /**
     * 審査情報編集画面
     *
     * @Route("/adoption/member/examination_info/{pet_type}", name="adoption_examination_info", methods={"GET","POST"})
     * @Template("/animalline/adoption/member/examination_info.twig")
     */
    public function examination_info(Request $request)
    {

        // 審査情報はアンケート的な意味合いで実装する。

        // $petType = $request->get('pet_type');
        // $conservation = $this->conservationsRepository->find($this->getUser());
        // $conservationExaminationInfo = $this->conservationExaminationInfoRepository->findOneBy([
        //     'Conservation' => $conservation,
        //     'pet_type' => $petType
        // ]);
        // $isEdit = false;
        // if ($conservationExaminationInfo) {
        //     $isEdit = true;
        //     if (in_array($conservationExaminationInfo->getPedigreeOrganization(),
        //         [AnilineConf::PEDIGREE_ORGANIZATION_JKC, AnilineConf::PEDIGREE_ORGANIZATION_KC])) {
        //         $conservationExaminationInfo->setGroupOrganization($conservationExaminationInfo->getPedigreeOrganization());
        //         $conservationExaminationInfo->setPedigreeOrganization(AnilineConf::PEDIGREE_ORGANIZATION_JKC);
        //     }
        // } else {
        //     $conservationExaminationInfo = new ConservationExaminationInfo();
        // }

        // $form = $this->createForm(ConservationExaminationInfoType::class, $conservationExaminationInfo);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $conservationExaminationInfo->setPetType($petType)
        //         ->setConservation($conservation);
        //     $formRequest = $request->request->get('adoption_examination_info');
        //     if ($formRequest['pedigree_organization'] == AnilineConf::PEDIGREE_ORGANIZATION_JKC) {
        //         $conservationExaminationInfo->setPedigreeOrganization($formRequest['group_organization']);
        //     } else {
        //         $conservationExaminationInfo->setPedigreeOrganization($formRequest['pedigree_organization']);
        //     }

        //     if ($formRequest['pedigree_organization'] != AnilineConf::PEDIGREE_ORGANIZATION_OTHER) {
        //         $conservationExaminationInfo->setPedigreeOrganizationOther(null);
        //     }

        //     $conservationExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_INPUT_COMPLETE);

        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($conservationExaminationInfo);
        //     $entityManager->flush();

        //     return $this->redirectToRoute('adoption_examination');
        // }

        // return $this->render('animalline/adoption/member/examination_info.twig', [
        //     'form' => $form->createView(),
        //     'isEdit' => $isEdit,
        //     'petType' => $petType == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫'
        // ]);
    }

    /**
     * 審査結果提出
     *
     * @Route("/adoption/member/examination/submit", name="adoption_examination_submit")
     */
    public function examination_submit(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // 保護団体の審査ステータスを変更
        $conservation = $this->conservationsRepository->find($this->getUser());
        $conservation->setExaminationStatus(AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK);

        $entityManager->persist($conservation);

        // // 犬舎・猫舎両方のパターンがあるため配列で取得
        // $conservationExaminationInfos = $this->conservationExaminationInfoRepository->findBy([
        //     'Conservation' => $conservation,
        // ]);

        // // 審査情報のそれぞれの審査ステータスを変更
        // foreach($conservationExaminationInfos as $conservationExaminationInfo){
        //     $conservationExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_SUBMIT);  
        //     $entityManager->persist($conservationExaminationInfo);
        // }

        $entityManager->flush();

        return $this->redirectToRoute('adoption_examination');
    }

    /**
     * お気に入り一覧画面
     *
     * @Route("/adoption/member/favorite", name="adoption_favorite")
     * @Template("animalline/adoption/favorite.twig")
     */
    public function favorite(PaginatorInterface $paginator, Request $request): ?Response
    {
        $favoritePetResults = $this->conservationPetsRepository->findByFavoriteCount();
        $favoritePets = $paginator->paginate(
            $favoritePetResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/adoption/favorite.twig', ['pets' => $favoritePets]);
    }


    /**
     * 保護団体用ユーザーページ - 取引メッセージ履歴
     *
     * @Route("/adoption/member/message/{id}/contract", name="adoption_message_contract", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/member/message.twig")
     */
    public function adoption_message_contract(ConservationContactHeader $rootMessage)
    {
        $currentStatus = $rootMessage->getContractStatus();
        if ($currentStatus === AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT);
        else if ($currentStatus === AnilineConf::CONTRACT_STATUS_WAITCONTRACT) $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT);
        $rootMessage->setCustomerCheck(1);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($rootMessage);
        $entityManager->flush();

        return $this->redirectToRoute('adoption_mypage_messages', ['id' => $rootMessage->getId()]);
    }

    /**
     * 保護団体用ユーザーページ - 取引メッセージ履歴
     *
     * @Route("/adoption/member/message/{id}/cancel", name="adoption_message_cancel", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/member/message.twig")
     */
    public function adoption_message_cancel(Request $request, ConservationContactHeader $rootMessage)
    {
        $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT);
        $rootMessage->setSendoffReason($request->get('reason'));

        $conservationContact = (new ConservationContacts())
            ->setConservationHeader($rootMessage)
            ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
            ->setContactDescription('今回の取引非成立となりました')
            ->setSendDate(new DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($rootMessage);
        $entityManager->persist($conservationContact);
        $entityManager->flush();

        return $this->redirectToRoute('adoption_mypage_messages', ['id' => $rootMessage->getId()]);
    }

    /**
     * お問い合わせ画面
     *
     * @Route("/adoption/member/contact/{pet_id}", name="adoption_contact", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/adoption/contact.twig")
     */
    public function contact(Request $request)
    {
        $id = $request->get('pet_id');
        $pet = $this->conservationPetsRepository->find($id);
        if (!$pet) {
            throw new HttpException\NotFoundHttpException();
        }

        $contact = new ConservationContactHeader();
        $builder = $this->formFactory->createBuilder(ConservationContactType::class, $contact);
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
                        'animalline/adoption/contact_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'id' => $id
                        ]
                    );

                case 'complete':
                    $contact
                        ->setSendDate(Carbon::now())
                        ->setPet($pet)
                        ->setConservation($pet->getConservation())
                        ->setCustomer($this->getUser())
                        ->setLastMessageDate(Carbon::now());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($contact);
                    $entityManager->flush();

                    return $this->redirectToRoute('adoption_contact_complete', ['pet_id' => $id]);
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
     * @Route("/adoption/member/pet_list", name="adoption_pet_list")
     * @Template("animalline/adoption/member/pet_list.twig")
     */
    public function adoption_pet_list(Request $request)
    {
        $pets = $this->conservationPetsRepository->findBy(['Conservation' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/adoption/member/pet_list.twig',
            [
                'conservation' => $this->getUser(),
                'pets' => $pets,
            ]
        );
    }

    /**
     * 検査状況確認
     *
     * @Route("/adoption/member/examination_status", name="adoption_examination_status")
     * @Template("animalline/adoption/member/examination_status.twig")
     */
    public function examination_status(Request $request, PaginatorInterface $paginator)
    {
        $dnaId = (int)$request->get('dna_id');
        if ($request->isMethod('POST') && $dnaId) {
            $dna = $this->dnaCheckStatusRepository->find($dnaId);
            if (!$dna) {
                throw new NotFoundHttpException();
            }

            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT);
            $newDna = clone $dna;
            $newDna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_DEFAULT);

            $em = $this->getDoctrine()->getManager();
            $em->persist($dna);
            $em->persist($newDna);
            $em->flush();

            return $this->redirectToRoute('adoption_examination_status');
        }

        $userId = $this->getUser()->getId();
        $isAll = $request->get('is_all') ?? false;
        // TODO:dnaQueryServiceにfilterDnaAdoptionMemberを実装
        $results = $this->dnaQueryService->filterDnaBreederMember($userId, $isAll);
        $dnas = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        return compact('dnas');
    }

    /**
     * 新規ペット追加
     *
     * @Route("/adoption/member/pets/new/{bar_code}", name="adoption_pets_new", methods={"GET","POST"}, requirements={"bar_code" = "^\d{6}$"})
     */
    public function adoption_pets_new(Request $request): Response
    {
        $barCode = substr($request->get('bar_code'), 1);
        $DnaId = (int)$barCode;
        $Dna = $this->dnaCheckStatusRepository->find($DnaId);
        if (!$Dna) {
            throw new HttpException\NotFoundHttpException();
        }
        $user = $this->getUser();
        $is_conservation = $user->getIsConservation();
        $DnaHeader = $Dna->getDnaHeader();
        $conservation = $this->conservationsRepository->find($DnaHeader->getRegisterId());
        if ($is_conservation == 0) {
            return $this->render('animalline/adopution/member/examination_guidance.twig', [
                'conservation' => $conservation
            ]);
        }

        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet, [
            'customer' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $conservationPet->setConservation($conservation);
            $conservationPet->setDnaCheckResult(0);
            $conservationPet->setReleaseStatus(0);
            $conservationPet->setPrice(0);
            $entityManager->persist($conservationPet);
            $entityManager->flush();
            $petId = $conservationPet->getId();
            $Dna->setPetId($petId)
                ->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED);
            $entityManager->persist($Dna);
            $entityManager->flush();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);

            $petImage0 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img0)->setSortOrder(1)
                ->setConservationPet($conservationPet);
            $petImage1 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setConservationPet($conservationPet);
            $petImage2 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setConservationPet($conservationPet);
            $petImage3 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setConservationPet($conservationPet);
            $petImage4 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setConservationPet($conservationPet);
            $conservationPet->addConservationPetImage($petImage0);
            $conservationPet->addConservationPetImage($petImage1);
            $conservationPet->addConservationPetImage($petImage2);
            $conservationPet->addConservationPetImage($petImage3);
            $conservationPet->addConservationPetImage($petImage4);
            $conservationPet->setThumbnailPath($img0);

            // $dnaCheckStatus = (new DnaCheckStatus)
            //     ->setRegisterId($conservation->getId())
            //     ->setPetId($conservationPet->getId())
            //     ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION);

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($conservationPet);
            // $entityManager->persist($dnaCheckStatus);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_newpet_complete');
        }

        return $this->render('animalline/adoption/member/pets/new.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * 新規ペット追加完了メッセージ
     *
     * @Route("/breeder/adoption/pets/new_complete", name="adoption_newpet_complete", methods={"GET","POST"})
     * @Template("animalline/adoption/member/pets/notification.twig")
     */
    public function adoption_pets_new_complete()
    {
        return [];
    }

    /**
     * ペット情報編集
     *
     * @Route("/adoption/member/pets/edit/{id}", name="adoption_pets_edit", methods={"GET","POST"})
     */
    public function adoption_pets_edit(Request $request, ConservationPets $conservationPet): Response
    {
        $form = $this->createForm(ConservationPetsType::class, $conservationPet, [
            'customer' => $this->getUser(),
        ]);
        $conservationPetImages = $this->conservationPetImageRepository->findBy(
            ['ConservationPet' => $conservationPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );
        $request->request->set('thumbnail_path', $conservationPet->getThumbnailPath());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petId = $conservationPet->getId();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);
            $entityManager = $this->getDoctrine()->getManager();
            $conservationPet->setThumbnailPath($img0);
            $entityManager->persist($conservationPet);
            foreach ($conservationPetImages as $key => $image) {
                $image->setImageUri(${'img' . $key});
                $entityManager->persist($image);
            }
            $entityManager->flush();

            return $this->redirectToRoute('adoption_pet_list');
        }

        $petImages = [];
        foreach ($conservationPetImages as $image) {
            $petImages[] = [
                'image_uri' => $image->getImageUri(),
                'sort_order' => $image->getSortOrder()
            ];
        }

        return $this->render('animalline/adoption/member/pets/edit.twig', [
            'adoption_pet' => $conservationPet,
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
    private function setImageSrc($imageUrl, $petId)
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
        if ($arr[0] === 'adoption') {
            return $resource;
        }

        $imageName = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/',
            '',
            $imageUrl
        );
        $subUrl = AnilineConf::ANILINE_IMAGE_URL_BASE . '/adoption/' . $petId . '/';
        if (!file_exists($subUrl)) {
            mkdir($subUrl, 0777, 'R');
        }

        copy($imageUrl, $subUrl . $imageName);
        return '/adoption/' . $petId . '/' . $imageName;
    }
}
