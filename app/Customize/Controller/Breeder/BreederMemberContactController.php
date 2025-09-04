<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\BreederEvaluations;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\BreederContacts;
use Customize\Entity\BreederContactHeader;
use Customize\Entity\BreederNopetContactHeader;
use Customize\Entity\BreederNopetContacts;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederNopetContactHeaderRepository;
use Customize\Repository\BreederNopetContactsRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Breeder\BreederContactType;
use Customize\Form\Type\Breeder\BreederEvaluationsType;
use Customize\Service\MailService;
use Customize\Form\Type\Breeder\BreederNoPetContactType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BreederMemberContactController extends AbstractController
{
    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var BreederNopetContactHeaderRepository
     */
    protected $breederNopetContactHeaderRepository;

    /**
     * @var BreederNopetContactsRepository
     */
    protected $breederNopetContactsRepository;
    
    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;


    /**
     * BreederController constructor.
     *
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param BreederContactsRepository $breederContactsRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreedersRepository $breedersRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param CustomerRepository $customerRepository
     * @param BreederEvaluationsRepository $breederEvaluationsRepository
     * @param BreederQueryService $breederQueryService
     * @param MailService $mailService
     * @param BreederNopetContactHeaderRepository $breederNopetContactHeaderRepository
     * @param BreederNopetContactsRepository $breederNopetContactsRepository
     */

    public function __construct(
        EntityManagerInterface $entityManager,
        BreederContactHeaderRepository $breederContactHeaderRepository,
        BreederContactsRepository      $breederContactsRepository,
        SendoffReasonRepository        $sendoffReasonRepository,
        BreedersRepository             $breedersRepository,
        BreederPetsRepository          $breederPetsRepository,
        CustomerRepository             $customerRepository,
        BreederEvaluationsRepository   $breederEvaluationsRepository,
        BreederQueryService            $breederQueryService,
        MailService                    $mailService,
        BreederNopetContactHeaderRepository $breederNopetContactHeaderRepository,
        BreederNopetContactsRepository $breederNopetContactsRepository,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->breederContactsRepository = $breederContactsRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->customerRepository = $customerRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->mailService = $mailService;
        $this->breederNopetContactHeaderRepository = $breederNopetContactHeaderRepository;
        $this->breederNopetContactsRepository = $breederNopetContactsRepository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * ユーザー側取引メッセージ一覧
     *
     * @Route("/breeder/member/all_message", name="breeder_all_message")
     * @Template("animalline/breeder/member/all_message.twig")
     */
    public function all_message()
    {
        $listMessages = $this->breederContactHeaderRepository->findBy(['Customer' => $this->getUser()], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/all_message.twig', [
            'listMessages' => $listMessages
        ]);
    }

    /**
     * ユーザー側取引メッセージ画面
     *
     * @Route("/breeder/member/message/{id}", name="breeder_message", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/member/message.twig")
     */
    public function message(Request $request, BreederContactHeader $msgHeader)
    {
        $msgHeader->setCustomerNewMsg(0);
        $lastMsg = $this->breederContactsRepository->findBy(['BreederContactHeader' => $msgHeader, 'message_from' => AnilineConf::MESSAGE_FROM_MEMBER]);
        $entityManager = $this->entityManager;
        foreach ($lastMsg as $item) {
            $item->setIsReading(AnilineConf::ANILINE_READ);
            $entityManager->persist($item);
        }
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            //受信ファイル処理
            $brochureFile = $_FILES['files']['tmp_name'];

            $newFilename = "";
            if($brochureFile){
                $newFilename = 'pcontact-'.uniqid().'.'.pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
                if(!file_exists("html/upload/contact/")){
                    mkdir("html/upload/contact/");
                }
                copy($brochureFile,"html/upload/contact/".$newFilename);
            }

            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederContactHeader($msgHeader)
                ->setImageFile($newFilename)
                ->setIsReading(AnilineConf::ANILINE_NOT_READING);

            $msgHeader->setBreederNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->entityManager;
            $entityManager->persist($breederContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $breeder = $this->customerRepository->find($msgHeader->getBreeder()->getId());
            $this->mailService->sendMailNoticeMsg($breeder, $breederContact);

            return $this->redirectToRoute('breeder_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setBreederNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription('今回の取引は非成立となりました。')
                ->setIsReading(0)
                ->setSendDate(Carbon::now())
                ->setBreederContactHeader($msgHeader);

            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->persist($breederContact);
            $entityManager->flush();

            $breeder = $this->customerRepository->find($msgHeader->getBreeder()->getId());
            $this->mailService->sendMailContractCancelToShop($breeder, $msgHeader, 1);
            return $this->redirectToRoute('breeder_message',['id' => $request->get('id'), 'isScroll' => true]);
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->breederContactsRepository->findBy(['BreederContactHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_breeder_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'breeder' => $msgHeader->getBreeder(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons
        ];
    }

    /**
     * 成約画面（ユーザー側）
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
                    $contract
                        ->setPet($pet)->setImagePath($thumbnail_path)
                        ->setIsActive(1)
                        ->setBreeder($pet->getBreeder())
                        ->setCustomer($this->getUser());
                    $entityManager = $this->entityManager;
                    $entityManager->persist($contract);
                    $entityManager->flush();

                    $pet = $this->breederPetsRepository->find($pet_id);
                    if (!$pet) {
                        throw new HttpException\NotFoundHttpException();
                    }
                    $pet->setIsContract(1);
                    $entityManager->persist($pet);
                    $entityManager->flush();

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
                    $entityManager = $this->entityManager;

                    $breeder_base = $this->customerRepository->find($breeder->getId());
                    switch ($msgHeader->getContractStatus()) {
                        case AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION:
                            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT);

                            $this->mailService->sendMailContractCheckToShop($breeder_base, $msgHeader, 1);
                            break;
                        case AnilineConf::CONTRACT_STATUS_WAITCONTRACT:
                            if ($msgHeader->getBreederCheck() == 1) {
                                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT);
                                $customer = $msgHeader->getCustomer();
                                if (!$customer->getRegisterId()) {
                                    $customer->setRegisterId($msgHeader->getBreeder()->getId())
                                        ->setSiteType(AnilineConf::SITE_CATEGORY_BREEDER);
                                    $entityManager->persist($customer);
                                    $entityManager->flush();
                                }

                                $this->mailService->sendMailContractCompleteToShop($breeder_base, $msgHeader, 1);
                                $this->mailService->sendMailContractCompleteToUser($msgHeader->getCustomer(), $msgHeader, 1);
                                /*
                                foreach ($msgHeader->getPet()->getBreederContactHeader() as $item) {
                                    if (!in_array($item->getContractStatus(), [AnilineConf::CONTRACT_STATUS_CONTRACT, AnilineConf::CONTRACT_STATUS_NONCONTRACT])) {
                                        $item->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT);

                                        $entityManager->persist($item);
                                        $breederContact = (new BreederContacts())
                                            ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                                            ->setContactDescription('今回の取引は非成立となりました。')
                                            ->setSendDate(Carbon::now())
                                            ->setBreederContactHeader($item);
                                        $entityManager->persist($breederContact);

                                        $this->mailService->sendMailContractCancel($item->getCustomer(), []);
                                    }
                                }
                                */
                            }
                            break;
                    }
                    $msgHeader->setCustomerCheck(1);
                    $entityManager->persist($breeder);
                    $entityManager->persist($msgHeader);
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
     * 成約完了画面（ユーザー側）
     *
     * @Route("/breeder/member/contract/complete/{pet_id}", name="breeder_contract_complete", requirements={"pet_id" = "\d+"})
     * @Template("animalline/breeder/member/contract_complete.twig")
     */
    public function contract_complete(Request $request)
    {
        return $this->render('animalline/breeder/member/contract_complete.twig');
    }

    /**
     * ブリーダー側取引メッセージ一覧
     *
     * @Route("/breeder/member/all_breeder_message", name="breeder_all_breeder_message")
     * @Template("animalline/breeder/member/all_breeder_message.twig")
     */
    public function all_breeder_message()
    {
        return $this->redirectToRoute('breeder_pet_list');
        /*
        $Breeder = $this->breedersRepository->find($this->getUser()->getId());

        $listMessages = $this->breederContactHeaderRepository->findBy(['Breeder' => $Breeder], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/all_breeder_message.twig', [
            'listMessages' => $listMessages
        ]);
        */
    }

    /**
     * ブリーダー側取引メッセージ画面
     *
     * @Route("/breeder/member/breeder_message/{id}", name="breeder_breeder_message", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/member/breeder_message.twig")
     */
    public function breeder_message(Request $request, BreederContactHeader $msgHeader)
    {
        $msgHeader->setBreederNewMsg(0);
        $lastMsg = $this->breederContactsRepository->findBy(['BreederContactHeader' => $msgHeader, 'message_from' => AnilineConf::MESSAGE_FROM_USER]);
        $entityManager = $this->entityManager;
        foreach ($lastMsg as $item) {
            $item->setIsReading(AnilineConf::ANILINE_READ);
            $entityManager->persist($item);
        }
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $isAcceptContract = $request->get('accept-contract');
        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            //受信ファイル処理
            $brochureFile = $_FILES['files']['tmp_name'];
            $newFilename = "";
            if($brochureFile){
                $newFilename = 'pcontact-'.uniqid().'.'.pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
                if(!file_exists("html/upload/contact/")){
                    mkdir("html/upload/contact/");
                }
                copy($brochureFile,"html/upload/contact/".$newFilename);
            }

            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederContactHeader($msgHeader)
                ->setImageFile($newFilename)
                ->setIsReading(AnilineConf::ANILINE_NOT_READING);

            $msgHeader->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->entityManager;
            $entityManager->persist($breederContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $this->mailService->sendMailNoticeMsg($msgHeader->getCustomer(), $breederContact);

            return $this->redirectToRoute('breeder_breeder_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setCustomerNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription('今回の取引は非成立となりました。')
                ->setSendDate(Carbon::now())
                ->setIsReading(0)
                ->setBreederContactHeader($msgHeader);

            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->persist($breederContact);
            $entityManager->flush();

            $this->mailService->sendMailContractCancelToUser($msgHeader->getCustomer(), $msgHeader, 1);
            return $this->redirectToRoute('breeder_all_breeder_message');
        }

        //成約処理
        if ($isAcceptContract) {
            $pet = $msgHeader->getPet();
            $breeder = $pet->getBreeder();

            $breeder_base = $this->customerRepository->find($breeder->getId());

            $pet->setIsContract(1);
            $entityManager->persist($pet);
            $entityManager->flush();

            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                //交渉中の場合は自身に成約フラグを立ててＰＯにメールを送る。
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setBreederCheck(1);

                //ペットオーナーに成約処理依頼メール
                $this->mailService->sendMailContractCheckToUser($msgHeader->getCustomer(), $msgHeader, 1);
            }
            elseif ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $msgHeader->getCustomerCheck() == 1) {
                //成約確認待ちの場合は成約完了なので、両者にメールを送信する。
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setBreederCheck(1);
                $customer = $msgHeader->getCustomer();
                if (!$customer->getRegisterId()) {
                    $customer->setRegisterId($msgHeader->getBreeder()->getId())
                        ->setSiteType(AnilineConf::SITE_CATEGORY_BREEDER);
                    $entityManager->persist($customer);
                    $entityManager->flush();
                }
                $this->mailService->sendMailContractCompleteToUser($msgHeader->getCustomer(),$msgHeader, 1);
                $this->mailService->sendMailContractCompleteToShop($breeder_base, $msgHeader, 1);

                //取引成立時に他のユーザーと取引中のメッセージがある場合、全て非成立とし、メールを送信する。
                /*
                foreach ($msgHeader->getPet()->getBreederContactHeader() as $item) {
                    if (!in_array($item->getContractStatus(), [AnilineConf::CONTRACT_STATUS_CONTRACT, AnilineConf::CONTRACT_STATUS_NONCONTRACT])) {
                        $item->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT);
                        $entityManager->persist($item);
                        $breederContact = (new BreederContacts())
                            ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                            ->setContactDescription('今回の取引は非成立となりました。')
                            ->setSendDate(Carbon::now())
                            ->setIsReading(0)
                            ->setBreederContactHeader($item);
                        $entityManager->persist($breederContact);

                        $this->mailService->sendMailContractCancel($item->getCustomer(), $breederContact);
                    }
                }
                */
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_all_breeder_message');
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->breederContactsRepository->findBy(['BreederContactHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_breeder_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'breeder' => $msgHeader->getBreeder(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons
        ];
    }

    /**
     * お問い合わせ画面
     *
     * @Route("/breeder/contact/{pet_id}", name="breeder_contact", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/breeder/contact.twig")
     */
    public function contact(Request $request)
    {
        $arrayLabel = ['問い合わせ', '見学希望', '返信'];
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
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE);

        $form = $builder->getForm();
        $form->handleRequest($request);
        //受信ファイル処理
        $newFilename = $request->get("newFilename");
        $brochureFile = $form->get('files')->getData();
                    
        if($brochureFile){
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = 'pcontact-'.uniqid().'.'.$brochureFile->guessExtension();

            $brochureFile->move(
                "html/upload/contact/",
                $newFilename
            );

            $builder->setData(["files" => "html/upload/contact/".$newFilename]);
        }

        $response = new Response();

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    //セッション変数をリセットして認証情報を取得する
                    if(!$this->getUser()){
                        $response->headers->setCookie(new Cookie('contact_save', true));
                        $response->headers->setCookie(new Cookie('contact_pet', $pet->getId()));
                        $response->headers->setCookie(new Cookie('contact_image', $newFilename));
                        $response->headers->setCookie(new Cookie('contact_title', $arrayLabel[$request->get('breeder_contact')['contact_type'] - 1]));
                        $response->headers->setCookie(new Cookie('contact_description', $form->get('contact_description')->getData()));
                        $response->headers->setCookie(new Cookie('booking_request', $form->get('booking_request')->getData()));
                        $response->headers->setCookie(new Cookie('contact_type', $request->get('breeder_contact')['contact_type']));
                    }

                    $is_auth = false;
                    if($this->getUser()){
                        $is_auth = true;
                    }

                    return $this->render(
                        'animalline/breeder/contact_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'id' => $id,
                            'newFilename' => $newFilename,
                            'is_auth' => $is_auth
                        ], $response
                    );

                case 'complete':
                    //未ログインの場合は新規登録画面に遷移
                    if(!$this->getUser()){
                        return $this->redirectToRoute('entry',["ReturnPath" => "breeder_top"]);
                    }

                    $contact
                        ->setSendDate(Carbon::now())
                        ->setPet($pet)
                        ->setBreeder($pet->getBreeder())
                        ->setCustomer($this->getUser())
                        ->setContactTitle($arrayLabel[$request->get('breeder_contact')['contact_type'] - 1])
                        ->setImageFile($newFilename)
                        ->setLastMessageDate(Carbon::now());
                    $entityManager = $this->entityManager;
                    $entityManager->persist($contact);

                    $pet->setIsContact(1);
                    $entityManager->persist($pet);

                    $entityManager->flush();
                    $breederContact = new BreederContacts();
                    $breeder = $this->customerRepository->find($contact->getBreeder()->getId());
                    $this->mailService->sendMailContractAccept($breeder, 1);

                    return $this->redirectToRoute('breeder_contact_complete', ['pet_id' => $id]);
            }
        }
        else {
            $response->headers->setCookie(new Cookie('contact_save', false));
            $response->headers->setCookie(new Cookie('contact_pet', ""));
            $response->headers->setCookie(new Cookie('contact_image', ""));
            $response->headers->setCookie(new Cookie('contact_title', ""));
            $response->headers->setCookie(new Cookie('contact_description', ""));
            $response->headers->setCookie(new Cookie('booking_request', ""));
            $response->headers->setCookie(new Cookie('contact_type', ""));
        }

        //ユーザー認証を外したので自分のペットかどうかの判定は削除
        //$isSelf = $this->getUser()->getId() === $pet->getBreeder()->getId();
        $isSelf = false;
        
        
        $isSold = (bool)$this->breederContactHeaderRepository->findOneBy(['Pet' => $pet, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT]);
        $isContacted = $this->breederContactHeaderRepository->checkContacted($this->getUser(), $pet);

        return $this->render(
            'animalline/breeder/contact.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
                'isSelf' => $isSelf,
                'isSold' => $isSold,
                'isContacted' => $isContacted,
                "newFilename" => $newFilename
            ], $response
        );
    }

    /**
     * お問い合わせ完了画面
     *
     * @Route("/breeder/member/contact/{pet_id}/complete", name="breeder_contact_complete", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/breeder/contact_complete.twig")
     */
    public function complete(Request $request)
    {
        $response = new Response();

        $response->headers->setCookie(new Cookie('contact_save', false));
        $response->headers->setCookie(new Cookie('contact_pet', ""));
        $response->headers->setCookie(new Cookie('contact_image', ""));
        $response->headers->setCookie(new Cookie('contact_title', ""));
        $response->headers->setCookie(new Cookie('contact_description', ""));
        $response->headers->setCookie(new Cookie('booking_request', ""));
        $response->headers->setCookie(new Cookie('contact_type', ""));

        return $this->render('animalline/breeder/contact_complete.twig', [
            'id' => $request->get('pet_id')
        ],$response);
    }

    /**
     * Delete message
     *
     * @Route("/breeder/member/message/delete", name="delete_message_breeder")
     *
     */
    public function deleteMessageContact(Request $request) {
        $msg = $this->breederContactsRepository->find($request->get('msgId'));
        $msgHeaderId = $msg->getBreederContactHeader()->getId();
        $entityManager = $this->entityManager;
        $msg->setIsDelete(AnilineConf::ANILINE_MESSAGE_DELETED);
        $entityManager->persist($msg);
        $entityManager->flush();
        if ($request->get('role') == AnilineConf::MESSAGE_MEMBER) {
            return $this->redirect($this->generateUrl('breeder_breeder_message', [
                'id' => $msgHeaderId
            ]));
        }

        return $this->redirect($this->generateUrl('breeder_message', [
            'id' => $msgHeaderId
        ]));
    }


    /**
     * お問い合わせ画面
     *
     * @Route("/breeder/member/nopet_contact/{breeder_id}", name="breeder_nopet_contact", requirements={"breeder_id" = "\d+"})
     * @Template("/animalline/breeder/nopet_contact.twig")
     */
    public function nopet_contact(Request $request,$breeder_id)
    {
        $contact = new BreederNopetContactHeader();
        $arrayLabel = ['問い合わせ', '見学希望'];

        $builder = $this->formFactory->createBuilder(BreederNoPetContactType::class, $contact);
        $event = new EventArgs(
            [
                'builder' => $builder,
                'contact' => $contact
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE);

        $form = $builder->getForm();
        $form->handleRequest($request);
        $newFilename = $request->get("newFilename");

        $maintitle = "お問い合わせ";
        
        //受信ファイル処理
        $newFilename = $request->get("newFilename");
        $brochureFile = $form->get('files')->getData();
                    
        if($brochureFile){
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = 'pcontact-'.uniqid().'.'.$brochureFile->guessExtension();

            $brochureFile->move(
                "html/upload/contact/",
                $newFilename
            );

            $builder->setData(["files" => "html/upload/contact/".$newFilename]);
        }

        $breeder = $this->breedersRepository->find($breeder_id);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render(
                        'animalline/breeder/nopet_contact_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'newFilename' => $newFilename,
                            "breeder" => $breeder
                        ]
                    );

                case 'complete':
                    $contact
                        ->setSendDate(Carbon::now())
                        ->setBreeder($breeder)
                        ->setCustomer($this->getUser())
                        ->setContactTitle($arrayLabel[$request->get('breeder_no_pet_contact')['contact_type'] - 1])
                        ->setImageFile($newFilename)
                        ->setLastMessageDate(Carbon::now());
                    $entityManager = $this->entityManager;
                    $entityManager->persist($contact);
                    $entityManager->flush();
                    $breederContact = new BreederContacts();
                    
                    $breeder_customer = $this->customerRepository->find($breeder->getId());
                    $this->mailService->sendMailNopetContractAccept($breeder_customer, 1);

                    return $this->redirectToRoute('breeder_nopet_contact_complete', ['breeder_id' => $breeder->getId()]);
            }
        }

        if(!$form->get('files')->isValid()){
            $newFilename = "";
        }

        return [
            'title' => $maintitle,
            'form' => $form->createView(),
            "newFilename" => $newFilename,
            "breeder" => $breeder
        ];
    }

    /**
     * お問い合わせ完了画面
     *
     * @Route("/breeder/member/nopet_contact/complete/{breeder_id}", name="breeder_nopet_contact_complete", requirements={"breeder_id" = "\d+"})
     * @Template("/animalline/breeder/nopet_contact_complete.twig")
     */
    public function nopet_complete(Request $request, $breeder_id)
    {
        return $this->render(
            'animalline/breeder/nopet_contact_complete.twig',
            [
                "breederId" => $breeder_id
            ]
        );
    }

    /**
     * ユーザー側ブリーダー問い合わせ取引メッセージ一覧
     *
     * @Route("/breeder/member/nopet_all_message", name="nopet_all_message")
     * @Template("animalline/breeder/member/nopet_all_message.twig")
     */
    public function nopet_all_message()
    {
        $listMessages = $this->breederNopetContactHeaderRepository->findBy(['Customer' => $this->getUser()], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/nopet_all_message.twig', [
            'listMessages' => $listMessages
        ]);
    }

    /**
     * ブリーダー側ペット問い合わせ取引メッセージ一覧
     *
     * @Route("/breeder/member/user_all_message/{pet_id}", name="user_all_message", requirements={"pet_id" = "\d+"})
     * @Template("animalline/breeder/member/user_all_message.twig")
     */
    public function user_all_message($pet_id)
    {
        $pet = $this->breederPetsRepository->find($pet_id);

        $listMessages = $this->breederContactHeaderRepository->findBy(['Pet' => $pet], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/user_all_message.twig', [
            'listMessages' => $listMessages
        ]);
    }

    /**
     * ブリーダー側ユーザー問い合わせ取引メッセージ一覧
     *
     * @Route("/breeder/member/nopet_user_all_message/{breeder_id}", name="nopet_user_all_message")
     * @Template("animalline/breeder/member/nopet_user_all_message.twig")
     */
    public function nopet_user_all_message(Request $request,string $breeder_id = "")
    {
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
        $listMessages = $this->breederNopetContactHeaderRepository->findBy(['Breeder' => $breeder], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/nopet_user_all_message.twig', [
            'listMessages' => $listMessages,
            'breeder_id' => $breeder_id
        ]);
    }

    /**
     * ユーザー側取引メッセージ画面
     *
     * @Route("/breeder/member/nopet_message/{id}", name="nopet_breeder_message", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/member/nopet_message.twig")
     */
    public function nopet_message(Request $request, BreederNopetContactHeader $msgHeader)
    {
        $msgHeader->setCustomerNewMsg(0);
        $lastMsg = $this->breederNopetContactsRepository->findBy(['breederNopetContactHeader' => $msgHeader, 'message_from' => AnilineConf::MESSAGE_FROM_MEMBER]);
        $entityManager = $this->entityManager;
        foreach ($lastMsg as $item) {
            $item->setIsReading(AnilineConf::ANILINE_READ);
            $entityManager->persist($item);
        }
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            //受信ファイル処理
            $brochureFile = $_FILES['files']['tmp_name'];

            $newFilename = "";
            if($brochureFile){
                $newFilename = 'pcontact-'.uniqid().'.'.pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
                if(!file_exists("html/upload/contact/")){
                    mkdir("html/upload/contact/");
                }
                copy($brochureFile,"html/upload/contact/".$newFilename);
            }

            $breederNopetContact = (new BreederNopetContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederNopetContactHeader($msgHeader)
                ->setImageFile($newFilename)
                ->setIsReading(AnilineConf::ANILINE_NOT_READING);

            $msgHeader->setBreederNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->entityManager;
            $entityManager->persist($breederNopetContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $breeder = $this->customerRepository->find($msgHeader->getBreeder()->getId());
            $this->mailService->sendMailNopetNoticeMsg($breeder, $breederNopetContact);

            return $this->redirectToRoute('nopet_breeder_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->breederNopetContactsRepository->findBy(['breederNopetContactHeader' => $msgHeader], ['send_date' => 'ASC']);

        return [
            'Customer' => $Customer,
            'breeder' => $msgHeader->getBreeder(),
            'message' => $msgHeader,
            'listMsg' => $listMsg
        ];
    }

    /**
     * ブリーダー側取引メッセージ画面
     *
     * @Route("/breeder/member/nopet_user_message/{id}/{breeder_id}", name="nopet_user_message", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/member/nopet_user_message.twig")
     */
    public function nopet_user_message(Request $request, BreederNopetContactHeader $msgHeader,$breeder_id = "")
    {

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

        if($msgHeader->getBreeder()->getId() != $user->getId()){
            throw new NotFoundHttpException();
        }

        $msgHeader->setBreederNewMsg(0);
        $lastMsg = $this->breederNopetContactsRepository->findBy(['breederNopetContactHeader' => $msgHeader, 'message_from' => AnilineConf::MESSAGE_FROM_USER]);
        $entityManager = $this->entityManager;
        foreach ($lastMsg as $item) {
            $item->setIsReading(AnilineConf::ANILINE_READ);
            $entityManager->persist($item);
        }
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $isAcceptContract = $request->get('accept-contract');
        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            //受信ファイル処理
            $brochureFile = $_FILES['files']['tmp_name'];
            $newFilename = "";
            if($brochureFile){
                $newFilename = 'pcontact-'.uniqid().'.'.pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
                if(!file_exists("html/upload/contact/")){
                    mkdir("html/upload/contact/");
                }
                copy($brochureFile,"html/upload/contact/".$newFilename);
            }

            $breederNopetContact = (new BreederNopetContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederNopetContactHeader($msgHeader)
                ->setImageFile($newFilename)
                ->setIsReading(AnilineConf::ANILINE_NOT_READING);

            $msgHeader->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->entityManager;
            $entityManager->persist($breederNopetContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $this->mailService->sendMailNopetNoticeMsg($msgHeader->getCustomer(), $breederNopetContact);

            return $this->redirectToRoute('nopet_user_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        
        $user = $this->getUser();
        $Customer = $this->customerRepository->find($msgHeader->getCustomer());
        $listMsg = $this->breederNopetContactsRepository->findBy(['breederNopetContactHeader' => $msgHeader], ['send_date' => 'ASC']);
        
        return [
            'Customer' => $Customer,
            'breeder' => $msgHeader->getBreeder(),
            'message' => $msgHeader,
            'listMsg' => $listMsg
        ];
    }
}
