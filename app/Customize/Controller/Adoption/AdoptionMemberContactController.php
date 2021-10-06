<?php

namespace Customize\Controller\Adoption;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationContacts;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\ConservationContactType;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\SendoffReasonRepository;
use DateTime;

class AdoptionMemberContactController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

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
     * AdoptionController constructor.
     *
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     */
    public function __construct(
        ConservationPetsRepository     $conservationPetsRepository,
        ConservationContactsRepository $conservationContactsRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        SendoffReasonRepository             $sendoffReasonRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
    }

    /**
     * お問い合わせ.
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

        return $this->redirectToRoute('adoption_message', ['id' => $rootMessage->getId()]);
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

        return $this->redirectToRoute('adoption_message', ['id' => $rootMessage->getId()]);
    }
}