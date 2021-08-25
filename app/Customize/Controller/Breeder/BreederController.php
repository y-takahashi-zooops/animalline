<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Service\BreederQueryService;
use Carbon\Carbon;
use Customize\Entity\BreederContacts;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Breeder\BreederContactType;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * BreederController constructor.
     *
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreederPetsRepository $breederPetsRepository,
     */
    public function __construct(
        BreederContactsRepository $breederContactsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederQueryService           $breederQueryService,
        PetsFavoriteRepository         $petsFavoriteRepository,
        SendoffReasonRepository         $sendoffReasonRepository,
        BreederPetsRepository $breederPetsRepository
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breederPetsRepository = $breederPetsRepository;
    }

    /**
     * @Route("/breeder/pet/search/result", name="breeder_pet_search_result")
     * @Template("animalline/breeder/pet/search_result.twig")
     */
    public function petSearchResult(PaginatorInterface $paginator, Request $request): Response
    {
        $petResults = $this->breederQueryService->searchPetsResult($request);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/breeder/pet/search_result.twig', ['pets' => $pets]);
    }

    /**
     * @Route("/breeder/member/", name="breeder_mypage")
     * @Template("animalline/breeder/member/index.twig")
     */
    public function breeder_mypage(Request $request)
    {
        $customerId = $this->getUser()->getId();
        $rootMessages = $this->breederContactsRepository
            ->findBy(
                [
                    'Customer' => $customerId,
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

        $pets = $this->breederQueryService->findBreederFavoritePets($customerId);

        return $this->render('animalline/breeder/member/index.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies,
            'pets' => $pets
        ]);
    }

    /**
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $breederPet = $this->breederPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$breederPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $this->breederPetImageRepository->findBy(
            [
                'BreederPets' => $id,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->breederPetImageRepository->findOneBy(
            [
                'BreederPets' => $id,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );

        return $this->render(
            'animalline/breeder/pet/detail.twig',
            [
                'breederPet' => $breederPet,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn
            ]
        );
    }

    /**
     * @Route("/breeder/member/all_message", name="breeder_get_message_mypage")
     * @Template("animalline/breeder/member/breeder_message.twig")
     */
    public function get_message_mypage(Request $request)
    {
        $customerId = $this->getUser()->getId();
        $rootMessages = $this->breederContactsRepository
            ->findBy(['Customer' => $customerId, 'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID]);

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
     * @Route("/breeder/pet/detail/favorite_pet", name="breeder_favorite_pet")
     * @param Request $request
     * @return JsonResponse
     */
    public function favoritePet(Request $request)
    {
        $id = $request->get('id');
        $pet = $this->breederPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$favorite) {
            $petKind = $pet->getPetKind();
            $favorite_pet = new PetsFavorite();
            $favorite_pet->setCustomer($this->getUser())
                ->setPetId($id)
                ->setSiteCategory(AnilineConf::SITE_CATEGORY_BREEDER)
                ->setPetKind($petKind);
            $entityManager->persist($favorite_pet);
            $entityManager->flush();

            $this->breederPetsRepository->incrementCount($pet);
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();

            $this->breederPetsRepository->decrementCount($pet);

            return new JsonResponse('unliked');
        }

        return new JsonResponse('liked');
    }

    /**
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
