<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationPets;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\ConservationsRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Adoption\ConservationContactType;
use Customize\Service\MailService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdoptionMemberContactController extends AbstractController
{
    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        ConservationContactsRepository $conservationContactsRepository,
        SendoffReasonRepository        $sendoffReasonRepository,
        ConservationsRepository        $conservationsRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        CustomerRepository             $customerRepository,
        MailService                    $mailService,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * ユーザー側取引メッセージ一覧
     *
     * @Route("/adoption/member/all_message", name="adoption_all_message")
     * @Template("animalline/adoption/member/all_message.twig")
     */
    public function all_message()
    {
        $listMessages = $this->conservationContactHeaderRepository->findBy(['Customer' => $this->getUser()], ['last_message_date' => 'DESC']);

        return $this->render('animalline/adoption/member/all_message.twig', [
            'listMessages' => $listMessages
        ]);
    }

    /**
     * ユーザー側取引メッセージ画面
     *
     * @Route("/adoption/member/message/{id}", name="adoption_message", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/member/message.twig")
     */
    public function message(Request $request, ConservationContactHeader $msgHeader)
    {
        $msgHeader->setCustomerNewMsg(0);
        $entityManager = $this->entityManager;
        $entityManager->persist($msgHeader);
        $lastMessages = $this->conservationContactsRepository->findBy(
            [
                'ConservationContactHeader' => $msgHeader,
                'message_from' => AnilineConf::MESSAGE_FROM_MEMBER
            ],
            ['id'=>'DESC']);

        foreach ($lastMessages as $lastMessage) {
            if ($lastMessage->getIsReading() == AnilineConf::ANILINE_NOT_READING) {
                $lastMessage->setIsReading(AnilineConf::ANILINE_READ);
            }
            $entityManager->persist($lastMessage);
        }
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

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setIsReading(0)
                ->setImageFile($newFilename)
                ->setConservationContactHeader($msgHeader);

            $msgHeader->setConservationNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->entityManager;
            $entityManager->persist($conservationContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            $conservation = $this->customerRepository->find($msgHeader->getConservation()->getId());

            $this->mailService->sendMailContractReply($conservation, $conservationContact);
            return $this->redirectToRoute('adoption_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setConservationNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription('今回の取引は非成立となりました。')
                ->setSendDate(Carbon::now())
                ->setIsReading(0)
                ->setConservationContactHeader($msgHeader);

            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->persist($conservationContact);
            $entityManager->flush();

            $conservation = $this->customerRepository->find($msgHeader->getConservation()->getId());
            $this->mailService->sendMailContractCancelToShop($conservation, $msgHeader, 2);

            return $this->redirectToRoute('adoption_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        if ($isAcceptContract) {
            $conservation = $this->customerRepository->find($msgHeader->getConservation()->getId());

            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setCustomerCheck(1);
                
                    $this->mailService->sendMailContractCheckToShop($conservation, $msgHeader, 2);
            }
            elseif ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $msgHeader->getConservationCheck() == 1) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setCustomerCheck(1);
                $customer = $msgHeader->getCustomer();
                if (!$customer->getRegisterId()) {
                    $customer->setRegisterId($msgHeader->getConservation()->getId())
                        ->setSiteType(AnilineConf::SITE_CATEGORY_CONSERVATION);
                    $entityManager->persist($customer);
                    $entityManager->flush();
                }

                $this->mailService->sendMailContractCompleteToShop($conservation, $msgHeader, 2);
                $this->mailService->sendMailContractCompleteToUser($msgHeader->getCustomer(), $msgHeader, 2);
                /*
                foreach ($msgHeader->getPet()->getConservationContactHeader() as $item) {
                    if (!in_array($item->getContractStatus(), [AnilineConf::CONTRACT_STATUS_CONTRACT, AnilineConf::CONTRACT_STATUS_NONCONTRACT])) {
                        $item->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT);
                        $entityManager->persist($item);
                        $conservationContact = (new ConservationContacts())
                            ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                            ->setContactDescription('今回の取引は非成立となりました。')
                            ->setSendDate(Carbon::now())
                            ->setIsReading(0)
                            ->setConservationContactHeader($item);
                        $entityManager->persist($conservationContact);

                        $this->mailService->sendMailContractCancel($item->getCustomer(), []);
                    }
                }
                */
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_all_message');
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->conservationContactsRepository->findBy(['ConservationContactHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_adoption_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'conservation' => $msgHeader->getConservation(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons
        ];
    }

    /**
     * 保護団体側取引メッセージ一覧
     *
     * @Route("/adoption/member/all_adoption_message/{id}", name="adoption_all_adoption_message", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/member/all_adoption_message.twig")
     */
    public function all_adoption_message(Request $request, ConservationPets $conservationPets)
    {
        $Conservation = $this->conservationsRepository->find($this->getUser()->getId());

        $listMessages = $this->conservationContactHeaderRepository->findBy(['Conservation' => $Conservation,'Pet' => $conservationPets], ['last_message_date' => 'DESC']);

        return $this->render('animalline/adoption/member/all_adoption_message.twig', [
            'listMessages' => $listMessages
        ]);
    }

    /**
     * 保護団体側取引メッセージ画面
     *
     * @Route("/adoption/member/adoption_message/{id}", name="adoption_adoption_message", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/member/adoption_message.twig")
     */
    public function adoption_message(Request $request, ConservationContactHeader $msgHeader)
    {
        $msgHeader->setConservationNewMsg(0);
        $entityManager = $this->entityManager;
        $entityManager->persist($msgHeader);
        $lastMessages = $this->conservationContactsRepository->findBy(
            [
                'ConservationContactHeader' => $msgHeader,
                'message_from' => AnilineConf::MESSAGE_FROM_USER
            ],
            ['id'=>'DESC']);

        foreach ($lastMessages as $lastMessage) {
            if ($lastMessage->getIsReading() == AnilineConf::ANILINE_NOT_READING) {
                $lastMessage->setIsReading(AnilineConf::ANILINE_READ);
            }
            $entityManager->persist($lastMessage);
        }
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

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setIsReading(0)
                ->setImageFile($newFilename)
                ->setConservationContactHeader($msgHeader);

            $msgHeader->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->entityManager;
            $entityManager->persist($conservationContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            $this->mailService->sendMailContractReply($msgHeader->getCustomer(), $conservationContact);
            return $this->redirectToRoute('adoption_adoption_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setCustomerNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription('今回の取引は非成立となりました。')
                ->setSendDate(Carbon::now())
                ->setIsReading(0)
                ->setConservationContactHeader($msgHeader);

            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->persist($conservationContact);
            $entityManager->flush();

            $this->mailService->sendMailContractCancelToUser($msgHeader->getCustomer(), $msgHeader, 2);

            return $this->redirectToRoute('adoption_adoption_message', ['id' => $request->get('id'), 'isScroll' => true]);
        }
        if ($isAcceptContract) {
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setConservationCheck(1);

                    $this->mailService->sendMailContractCheckToUser($msgHeader->getCustomer(), $msgHeader, 2);
            }
            elseif ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $msgHeader->getCustomerCheck() == 1) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setConservationCheck(1);
                $customer = $msgHeader->getCustomer();
                if (!$customer->getRegisterId()) {
                    $customer->setRegisterId($msgHeader->getConservation()->getId())
                        ->setSiteType(AnilineConf::SITE_CATEGORY_CONSERVATION);
                    $entityManager->persist($customer);
                    $entityManager->flush();
                }

                $conservation = $this->customerRepository->find($msgHeader->getConservation()->getId());
                $this->mailService->sendMailContractCompleteToUser($msgHeader->getCustomer(), $msgHeader, 2);
                $this->mailService->sendMailContractCompleteToShop($conservation, $msgHeader, 2);

                /*
                foreach ($msgHeader->getPet()->getConservationContactHeader() as $item) {
                    if (!in_array($item->getContractStatus(), [AnilineConf::CONTRACT_STATUS_CONTRACT, AnilineConf::CONTRACT_STATUS_NONCONTRACT])) {
                        $item->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT);
                        $entityManager->persist($item);
                        $conservationContact = (new ConservationContacts())
                            ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                            ->setContactDescription('今回の取引は非成立となりました。')
                            ->setSendDate(Carbon::now())
                            ->setIsReading(0)
                            ->setConservationContactHeader($item);
                        $entityManager->persist($conservationContact);

                        $this->mailService->sendMailContractCancel($item->getCustomer(), []);
                    }
                }
                */
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_all_adoption_message');
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->conservationContactsRepository->findBy(['ConservationContactHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_adoption_visible' => AnilineConf::ADOPTION_VISIBLE_SHOW]);

        return [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'conservation' => $msgHeader->getConservation(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons
        ];
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
        $conservationContact = new ConservationContacts();
        $builder = $this->formFactory->createBuilder(ConservationContactType::class, $contact);
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

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render(
                        'animalline/adoption/contact_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'id' => $id,
                            'newFilename' => $newFilename
                        ]
                    );

                case 'complete':
                    $arr = ['問い合わせ', '見学希望', '返信'];
                    $contact
                        ->setSendDate(Carbon::now())
                        ->setPet($pet)
                        ->setConservation($pet->getConservation())
                        ->setCustomer($this->getUser())
                        ->setContactTitle($arr[$request->get('conservation_contact')['contact_type'] - 1])
                        ->setImageFile($newFilename)
                        ->setLastMessageDate(Carbon::now());
                    $entityManager = $this->entityManager;
                    $entityManager->persist($contact);
                    $entityManager->flush();

                    $conservation = $this->customerRepository->find($contact->getConservation()->getId());
                    $this->mailService->sendMailContractAccept($conservation, 2);
                    return $this->redirectToRoute('adoption_contact_complete', ['pet_id' => $id]);
            }
        }

        $isSelf = $this->getUser()->getId() === $pet->getConservation()->getId();
        $isSold = (bool)$this->conservationContactHeaderRepository->findOneBy(['Pet' => $pet, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT]);
        $isContacted = $this->conservationContactHeaderRepository->checkContacted($this->getUser(), $pet);

        return [
            'form' => $form->createView(),
            'id' => $id,
            'isSelf' => $isSelf,
            'isSold' => $isSold,
            'isContacted' => $isContacted,
            'newFilename' => $newFilename
        ];
    }

    /**
     * お問い合わせ完了画面
     *
     * @Route("/adoption/member/contact/{pet_id}/complete", name="adoption_contact_complete", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/adoption/contact_complete.twig")
     */
    public function complete(Request $request)
    {
        return $this->render('animalline/adoption/contact_complete.twig', [
            'id' => $request->get('pet_id')
        ]);
    }

    /**
     * Delete message
     *
     * @Route("/adoption/member/message/delete", name="delete_message_adoption")
     *
     */
    public function deleteMessageContact(Request $request) {
        $msg = $this->conservationContactsRepository->find($request->get('msgId'));
        $msgHeaderId = $msg->getConservationContactHeader()->getId();
        $entityManager = $this->entityManager;
        $msg->setIsDelete(AnilineConf::ANILINE_MESSAGE_DELETED);
        $entityManager->persist($msg);
        $entityManager->flush();
        if ($request->get('role') == AnilineConf::MESSAGE_MEMBER) {
            return $this->redirect($this->generateUrl('adoption_adoption_message', [
                'id' => $msgHeaderId
            ]));
        }

        return $this->redirect($this->generateUrl('adoption_message', [
            'id' => $msgHeaderId
        ]));
    }
}
