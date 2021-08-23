<?php

namespace Customize\Controller\Breeder;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederContacts;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Breeder\BreederContactType;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;

class BreederController extends AbstractController
{
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
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * BreederController constructor.
     *
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederPetImageRepository $breederPetImageRepository ,
     * @param BreederContactsRepository $breederContactsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     */
    public function __construct(
        BreederPetsRepository     $breederPetsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederContactsRepository $breederContactsRepository,
        PetsFavoriteRepository    $petsFavoriteRepository,
        SendoffReasonRepository   $sendoffReasonRepository
    )
    {
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederContactsRepository = $breederContactsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
    }

    /**
     * ペット詳細.
     *
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $breederPet = $this->breederPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['customer_id' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$breederPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $this->breederPetImageRepository->findBy(
            [
                'breeder_pet_id' => $id,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->breederPetImageRepository->findOneBy(
            [
                'breeder_pet_id' => $id,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );

        return $this->render(
            'animalline/breeder/pet/detail.twig',
            [
                'conservationPet' => $breederPet,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn
            ]
        );
    }

    /**
     * @Route("/breeder/member/all_message", name="get_message_mypage")
     * @Template("animalline/breeder/member/breeder_message.twig")
     */
    public function get_message_mypage(Request $request)
    {
        $customerId = $this->getUser()->getId();
        $rootMessages = $this->breederContactsRepository
            ->findBy(['customer' => $customerId, 'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID]);

        $lastReplies = [];
        foreach ($rootMessages as $rootMessage) {
            $lastReply = $this->breederContactsRepository
                ->findOneBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'DESC']);
            $lastReplies[$rootMessage->getId()] = $lastReply;
        }

        return $this->render('animalline/breeder/member/breeder_message.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies
        ]);
    }

    /**
     * 保護団体用ユーザーページ
     *
     * @Route("/breeder/member/", name="breeder_mypage")
     * @Template("animalline/breeder/member/index.twig")
     */
    public function breeder_mypage(Request $request)
    {
        $customerId = $this->getUser()->getId();
        $rootMessages = $this->breederContactsRepository
            ->findBy(
                [
                    'customer' => $customerId,
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

        return $this->render('animalline/breeder/member/index.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies
        ]);
    }

    /**
     * 保護団体用ユーザーページ - 取引メッセージ履歴
     *
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
            $breederContact = (new breederContacts())
                ->setCustomer($this->getUser())
                ->setbreeder($rootMessage->getbreeder())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setPet($rootMessage->getPet())
                ->setContactType(AnilineConf::CONTACT_TYPE_REPLY)
                ->setContactDescription($replyMessage)
                ->setParentMessageId($rootMessage->getId())
                ->setSendDate(new DateTime())
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
     * お問い合わせ.
     *
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

        $contact = new BreederContacts();
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
                    $contact->setParentMessageId(AnilineConf::ROOT_MESSAGE_ID)
                        ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                        ->setIsResponse(AnilineConf::RESPONSE_UNREPLIED)
                        ->setSendDate(Carbon::now())
                        ->setPet($pet)
                        ->setBreeder($pet->getBreeder())
                        ->setCustomer($this->getUser());
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
