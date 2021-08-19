<?php

namespace Customize\Controller\Adoption;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContacts;
use Customize\Entity\PetsFavorite;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\ConservationContactType;
use Customize\Service\AdoptionQueryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

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
     * AdoptionController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository,
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     */
    public function __construct(
        ConservationPetsRepository     $conservationPetsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationContactsRepository $conservationContactsRepository,
        AdoptionQueryService           $adoptionQueryService,
        PetsFavoriteRepository         $petsFavoriteRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
    }

    /**
     * ペット検索画面.
     *
     * @Route("/adoption/pet/search", name="adoption_pet_search")
     * @Template("animalline/adoption/pet/search.twig")
     */
    public function petSearch(Request $request)
    {
        return;
    }

    /**
     * ペット検索結果.
     *
     * @Route("/adoption/pet/search/result", name="adoption_pet_search_result")
     * @Template("animalline/adoption/pet/search_result.twig")
     */
    public function petSearchResult(PaginatorInterface $paginator, Request $request, ConservationPetsRepository $conservationPetsRepository): Response
    {
        $petResults = $this->adoptionQueryService->searchPetsResult($request);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/adoption/pet/search_result.twig', ['pets' => $pets]);
    }

    /**
     * ペット詳細.
     *
     * @Route("/adoption/pet/detail/{id}", name="adoption_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $conservationPet = $this->conservationPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['customer_id' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$conservationPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $this->conservationPetImageRepository->findBy(['conservation_pet_id' => $conservationPet->getId(), 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE]);

        return $this->render(
            'animalline/adoption/pet/detail.twig',
            [
                'conservationPet' => $conservationPet,
                'images' => $images,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn
            ]
        );
    }

    /**
     * @Route("/adoption/pet/detail/favorite_pet", name="favorite_pet")
     * @param Request $request
     * @return JsonResponse
     */
    public function favoritePet(Request $request)
    {
        $id = $request->get('id');
        $pet = $this->conservationPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['customer_id' => $this->getUser(), 'pet_id' => $id]);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$favorite) {
            $petKind = $pet->getPetKind();
            $favorite_pet = new PetsFavorite();
            $favorite_pet->setCustomerId($this->getUser())
                ->setPetId($id)
                ->setSiteCategory(AnilineConf::SITE_CATEGORY_CONSERVATION)
                ->setPetKind($petKind);
            $entityManager->persist($favorite_pet);
            $entityManager->flush();

            $this->conservationPetsRepository->incrementCount($pet);
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();

            $this->conservationPetsRepository->decrementCount($pet);

            return new JsonResponse('unliked');
        }

        return new JsonResponse('liked');
    }


    /**
     * よくある質問.
     *
     * @Route("/adoption/faq", name="adoption_faq")
     * @Template("animalline/adoption/faq.twig")
     */
    public function faq(Request $request)
    {
        return;
    }

    /**
     * サイト説明。初めての方へ.
     *
     * @Route("/adoption/readfirst", name="adoption_readfirst")
     * @Template("animalline/adoption/readfirst.twig")
     */
    public function readfirst(Request $request)
    {
        return;
    }

    /**
     * 最近見た子犬.
     *
     * @Route("/adoption/viewhist", name="adoption_viewhist")
     * @Template("animalline/adoption/viewhist.twig")
     */
    public function viewhist(Request $request)
    {
        return;
    }

    /**
     * お気に入り一覧.
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
     * 保護団体リスト.
     *
     * @Route("/adoption/list", name="adoption_list")
     * @Template("animalline/adoption/list.twig")
     */
    public function list(Request $request)
    {
        return;
    }


    /**
     * 保護団体用ユーザーページ
     *
     * @Route("/adoption/member/", name="adoption_mypage")
     * @Template("animalline/adoption/member/index.twig")
     */
    public function adoption_mypage(Request $request)
    {
        $customerId = $this->getUser()->getId();
        $rootMessages = $this->conservationContactsRepository
            ->findBy(['Customer' => $customerId, 'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID]);

        $lastReplies = [];
        foreach ($rootMessages as $rootMessage) {
            $lastReply = $this->conservationContactsRepository
                ->findOneBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'DESC']);
            $lastReplies[$rootMessage->getId()] = $lastReply;
        }

        return $this->render('animalline/adoption/member/index.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies
        ]);
    }

    /**
     * 保護団体用ユーザーページ - 取引メッセージ履歴
     *
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
        if ($replyMessage) {
            $conservationContact = (new ConservationContacts())
                ->setCustomer($this->getUser())
                ->setConservation($rootMessage->getConservation())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setPet($rootMessage->getPet())
                ->setContactType(AnilineConf::CONTACT_TYPE_REPLY)
                ->setContactDescription($replyMessage)
                ->setParentMessageId($rootMessage->getId())
                ->setSendDate(new DateTime())
                ->setIsResponse(AnilineConf::RESPONSE_UNREPLIED)
                ->setContractStatus(AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION)
                ->setReason(0);

            $rootMessage->setIsResponse(AnilineConf::RESPONSE_UNREPLIED);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationContact);
            $entityManager->persist($rootMessage);
            $entityManager->flush();
        }

        $childMessages = $this->conservationContactsRepository
            ->findBy(['parent_message_id' => $rootMessage->getId()], ['send_date' => 'ASC']);

        return $this->render('animalline/adoption/member/message.twig', [
            'rootMessage' => $rootMessage,
            'childMessages' => $childMessages
        ]);
    }

    /**
     * お問い合わせ.
     *
     * @Route("/adoption/member/contact/{pet_id}", name="adpotion_contact", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/adoption/contact.twig")
     */
    public function contact(Request $request)
    {
        $id = $request->get('pet_id');
        $contact = new ConservationContacts();
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
                    $pet = $this->conservationPetsRepository->find($id);
                    if (!$pet) {
                        throw new HttpException\NotFoundHttpException();
                    }
                    $contact->setParentMessageId(AnilineConf::ROOT_MESSAGE_ID)
                        ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                        ->setIsResponse(AnilineConf::RESPONSE_UNREPLIED)
                        ->setSendDate(Carbon::now())
                        ->setPet($pet)
                        ->setConservation($pet->getConservationId())
                        ->setCustomer($this->getUser());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($contact);
                    $entityManager->flush();

                    return $this->redirectToRoute('adpotion_contact_complete', ['pet_id' => $id]);
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
     * @Route("/adoption/member/contact/{pet_id}/complete", name="adpotion_contact_complete", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/adoption/contact_complete.twig")
     */
    public function complete(Request $request)
    {
        return $this->render('animalline/adoption/contact_complete.twig', [
            'id' => $request->get('pet_id')
        ]);
    }
}
