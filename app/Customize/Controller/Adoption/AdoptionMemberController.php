<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Service\AdoptionQueryService;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\ConservationsType;
use Customize\Form\Type\ConservationHouseType;
use Customize\Entity\Conservations;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationsHouse;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
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

class AdoptionMemberController extends AbstractController
{
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
     * @var ConservationsHouseRepository
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
     * ConservationController constructor.
     *
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
	 * @param ConservationsRepository $conservationsRepository
	 * @param ConservationsHousesRepository $conservationsHouseRepository
	 * @param ConservationPetsRepository $conservationPetsRepository
	 * @param CustomerRepository $customerRepository
     */
    public function __construct(
        ConservationContactsRepository $conservationContactsRepository,
        AdoptionQueryService $adoptionQueryService,
        PetsFavoriteRepository $petsFavoriteRepository,
        SendoffReasonRepository $sendoffReasonRepository,
        ConservationsRepository $conservationsRepository,
        PrefRepository $prefRepository,
		ConservationsHousesRepository $conservationsHouseRepository,
		ConservationPetsRepository $conservationPetsRepository,
		CustomerRepository $customerRepository
    )
    {
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->prefRepository = $prefRepository;
		$this->conservationsHouseRepository = $conservationsHouseRepository;
		$this->conservationPetsRepository = $conservationPetsRepository;
		$this->customerRepository = $customerRepository;
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
        /*
        $rootMessages = $this->conservationContactsRepository
            ->findBy(
                [
                    'Customer' => $this->getUser(),
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
        */

        $pets = $this->adoptionQueryService->findAdoptionFavoritePets($this->getUser()->getId());

        return $this->render('animalline/adoption/member/index.twig', [
            //'rootMessages' => $rootMessages,
            //'lastReplies' => $lastReplies,
            'pets' => $pets,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * 取引メッセージ一覧
     * 
     * @Route("/adoption/member/all_message", name="adoption_get_message_mypage")
     * @Template("animalline/adoption/member/adoption_message.twig")
     */
    public function get_message_mypage(Request $request)
    {
        $rootMessages = $this->conservationContactsRepository
            ->findBy(['Customer' => $this->getUser(), 'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID]);

        $lastReplies = [];
        foreach ($rootMessages as $rootMessage) {
            $lastReply = $this->conservationContactsRepository
                ->findOneBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'DESC']);
            $lastReplies[$rootMessage->getId()] = $lastReply;
        }

        return $this->render('animalline/adoption/member/adoption_message.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies
        ]);
    }

    /**
     * ブリーダー登録申請画面
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
		if($conservation){
			$step = 2;

			// 基本情報の取扱ペットに対応する犬舎・猫舎情報が登録されていればSTEP3を表示
			$handling_pet_kind = $conservation->getHandlingPetKind();
			$dog_house_info = $this->conservationsHouseRepository->findOneBy(["Conservation" => $conservation,"pet_type" => 1]);
			$cat_house_info = $this->conservationsHouseRepository->findOneBy(["Conservation" => $conservation,"pet_type" => 2]);

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
            if($handling_pet_kind == 0 && $cat_house_info && $dog_house_info){$step = 4;}
			if($handling_pet_kind == 1 && $dog_house_info){$step = 4;}
			if($handling_pet_kind == 2 && $cat_house_info){$step = 4;}

			// 審査申請済であればSTEP5として審査中メッセージ
            $examination_status = $conservation->getExaminationStatus();
            if($examination_status == 1){$step = 5;}

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
        if(!$conservation){
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
                ->setCity($addr['city'])
                ->setAddress($addr['address'])
                ->setThumbnailPath($thumbnail_path);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();
            return $this->redirectToRoute('adoption_examination');
        } elseif(!$form->isSubmitted()) {

            // Customer情報から初期情報をセット
            $Customer = $this->customerRepository->find($user);
            $form->get('owner_name')->setData($Customer->getname01().$Customer->getname02());
            $form->get('owner_kana')->setData($Customer->getkana01().$Customer->getkana02());
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
		if(!$conservationsHouse){
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
     * @Route("/adoption/member/message/{contact_id}", name="adoption_mypage_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/adoption/member/message.twig")
     */
    public function adoption_message(Request $request)
    {
        $contactId = $request->get('contact_id');
        $rootMessage = $this->conservationContactsRepository
            ->findOneBy(['id' => $contactId, 'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID]);
        if (!$rootMessage) {
            throw new HttpException\NotFoundHttpException();
        }

        $replyMessage = $request->get('reply_message');
        $isEnd = $request->get('end_negotiation');
        if ($replyMessage || $isEnd) {
            $conservationContact = (new ConservationContacts())
                ->setCustomer($this->getUser())
                ->setconservation($rootMessage->getConservation())
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
            $entityManager->persist($conservationContact);
            $entityManager->persist($rootMessage);
            $entityManager->flush();
        }

        $childMessages = $this->conservationContactsRepository
            ->findBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository
            ->findBy(['is_conservation_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return $this->render('animalline/adoption/member/message.twig', [
            'rootMessage' => $rootMessage,
            'childMessages' => $childMessages,
            'reasons' => $reasons
        ]);
    }

    /**
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
                        ->setCustomer($this->getUser());
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
}
