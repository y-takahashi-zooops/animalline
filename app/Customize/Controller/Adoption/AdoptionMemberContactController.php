<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationContactHeader;
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

use DateTime;

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
     * AdoptionController constructor.
     *
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        ConservationContactsRepository $conservationContactsRepository,
        SendoffReasonRepository        $sendoffReasonRepository,
        ConservationsRepository        $conservationsRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        CustomerRepository             $customerRepository
    ) {
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->customerRepository = $customerRepository;
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
        $isScroll = false;
        $msgHeader->setCustomerNewMsg(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $isAcceptContract = $request->get('accept-contract');
        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setConservationHeader($msgHeader);

            $msgHeader->setConservationNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $isScroll = true;
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setConservationNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription('今回の取引は非成立となりました')
                ->setSendDate(Carbon::now())
                ->setConservationHeader($msgHeader);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->persist($conservationContact);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_all_message');
        }
        if ($isAcceptContract) {
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setCustomerCheck(1);
            }
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $msgHeader->getConservationCheck() == 1) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setCustomerCheck(1);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_all_message');
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->conservationContactsRepository->findBy(['ConservationHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_adoption_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return $this->render('animalline/adoption/member/message.twig', [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'conservation' => $msgHeader->getConservation(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons,
            'isScroll' => $isScroll
        ]);
    }

    /**
     * 保護団体側取引メッセージ一覧
     *
     * @Route("/adoption/member/all_adoption_message", name="adoption_all_adoption_message")
     * @Template("animalline/adoption/member/all_adoption_message.twig")
     */
    public function all_adoption_message()
    {
        $Conservation = $this->conservationsRepository->find($this->getUser()->getId());

        $listMessages = $this->conservationContactHeaderRepository->findBy(['Conservation' => $Conservation], ['last_message_date' => 'DESC']);

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
        $isScroll = false;
        $msgHeader->setConservationNewMsg(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($msgHeader);
        $entityManager->flush();

        $isAcceptContract = $request->get('accept-contract');
        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        if ($replyMessage) {
            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription($replyMessage)
                ->setSendDate(Carbon::now())
                ->setConservationHeader($msgHeader);

            $msgHeader->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationContact);
            $entityManager->persist($msgHeader);
            $entityManager->flush();
            $isScroll = true;
        }
        if ($reasonCancel) {
            $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setCustomerNewMsg(1)
                ->setSendoffReason($reasonCancel)
                ->setLastMessageDate(Carbon::now());

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription('今回の取引は非成立となりました')
                ->setSendDate(Carbon::now())
                ->setConservationHeader($msgHeader);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->persist($conservationContact);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_all_adoption_message');
        }
        if ($isAcceptContract) {
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setConservationCheck(1);
            }
            if ($msgHeader->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $msgHeader->getCustomerCheck() == 1) {
                $msgHeader->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setConservationCheck(1);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($msgHeader);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_all_adoption_message');
        }

        $user = $this->getUser();
        $Customer = $this->customerRepository->find($user);
        $listMsg = $this->conservationContactsRepository->findBy(['ConservationHeader' => $msgHeader], ['send_date' => 'ASC']);
        $reasons = $this->sendoffReasonRepository->findBy(['is_adoption_visible' => AnilineConf::ADOPTION_VISIBLE_SHOW]);

        return $this->render('animalline/adoption/member/adoption_message.twig', [
            'Customer' => $Customer,
            'pet' => $msgHeader->getPet(),
            'conservation' => $msgHeader->getConservation(),
            'message' => $msgHeader,
            'listMsg' => $listMsg,
            'reasons' => $reasons,
            'isScroll' => $isScroll
        ]);
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
}
