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
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\BreedersRepository;
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
     */

    public function __construct(
        BreederContactHeaderRepository $breederContactHeaderRepository,
        BreederContactsRepository      $breederContactsRepository,
        SendoffReasonRepository        $sendoffReasonRepository,
        BreedersRepository             $breedersRepository,
        BreederPetsRepository          $breederPetsRepository,
        CustomerRepository             $customerRepository,
        BreederEvaluationsRepository   $breederEvaluationsRepository,
        BreederQueryService            $breederQueryService,
        MailService                    $mailService
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
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($lastMsg as $item) {
            $item->setIsReading(AnilineConf::ANILINE_READ);
            $entityManager->persist($item);
        }
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederContactHeader($msgHeader)
                ->setIsReading(AnilineConf::ANILINE_NOT_READING);

            $msgHeader->setBreederNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->getDoctrine()->getManager();
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
                ->setSendDate(Carbon::now())
                ->setBreederContactHeader($msgHeader);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->persist($breederContact);
            $entityManager->flush();

            $this->mailService->sendMailContractCancel($msgHeader->getCustomer(), []);
            return $this->redirectToRoute('breeder_message');
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
        $entityManager = $this->getDoctrine()->getManager();
        switch ($msgHeader->getContractStatus()) {
            case AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION:
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT);
                break;
            case AnilineConf::CONTRACT_STATUS_WAITCONTRACT:
                if ($msgHeader->getBreederCheck() == 1) {
                    $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT);
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
                }
                break;
        }
        $msgHeader->setCustomerCheck(1);
        $entityManager->persist($breeder);
        $entityManager->persist($msgHeader);
        $entityManager->flush();

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
        $Breeder = $this->breedersRepository->find($this->getUser()->getId());

        $listMessages = $this->breederContactHeaderRepository->findBy(['Breeder' => $Breeder], ['last_message_date' => 'DESC']);

        return $this->render('animalline/breeder/member/all_breeder_message.twig', [
            'listMessages' => $listMessages
        ]);
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
        $entityManager = $this->getDoctrine()->getManager();
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
            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setBreederContactHeader($msgHeader)
                ->setIsReading(AnilineConf::ANILINE_NOT_READING);

            $msgHeader->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->getDoctrine()->getManager();
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
                ->setBreederContactHeader($msgHeader);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->persist($breederContact);
            $entityManager->flush();

            $this->mailService->sendMailContractCancel($msgHeader->getCustomer(), []);
            return $this->redirectToRoute('breeder_all_message');
        }
        if ($isAcceptContract) {
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setBreederCheck(1);
            }
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $msgHeader->getCustomerCheck() == 1) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setBreederCheck(1);
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
            }
            $entityManager = $this->getDoctrine()->getManager();
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
     * @Route("/breeder/member/contact/{pet_id}", name="breeder_contact", requirements={"pet_id" = "\d+"})
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
                        ->setContactTitle($arrayLabel[$request->get('breeder_contact')['contact_type'] - 1])
                        ->setLastMessageDate(Carbon::now());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($contact);
                    $entityManager->flush();
                    $breederContact = new BreederContacts();
                    $breeder = $this->customerRepository->find($contact->getBreeder()->getId());
                    $this->mailService->sendMailNoticeMsg($breeder, $breederContact);

                    return $this->redirectToRoute('breeder_contact_complete', ['pet_id' => $id]);
            }
        }

        $isSelf = $this->getUser()->getId() === $pet->getBreeder()->getId();
        $isSold = (bool)$this->breederContactHeaderRepository->findOneBy(['Pet' => $pet, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT]);
        $isContacted = $this->breederContactHeaderRepository->checkContacted($this->getUser(), $pet);

        return [
            'form' => $form->createView(),
            'id' => $id,
            'isSelf' => $isSelf,
            'isSold' => $isSold,
            'isContacted' => $isContacted
        ];
    }

    /**
     * お問い合わせ完了画面
     *
     * @Route("/breeder/member/contact/{pet_id}/complete", name="breeder_contact_complete", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/breeder/contact_complete.twig")
     */
    public function complete(Request $request)
    {
        return $this->render('animalline/breeder/contact_complete.twig', [
            'id' => $request->get('pet_id')
        ]);
    }
}
