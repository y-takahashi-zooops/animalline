<?php

namespace Customize\Controller\Breeder;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContacts;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationPetImageRepository;
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
use Customize\Form\Type\ConservationContactType;
use Customize\Service\BreederQueryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;

class BreederController extends AbstractController
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
     * AdoptionController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository ,
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     */
    public function __construct(
        ConservationPetsRepository     $conservationPetsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        BreederContactsRepository $breederContactsRepository,
        BreederQueryService           $breederQueryService,
        PetsFavoriteRepository         $petsFavoriteRepository,
        SendoffReasonRepository         $sendoffReasonRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
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

        $pets = $this->breederQueryService->findBreederFavoritePets($customerId);

        return $this->render('animalline/breeder/member/index.twig', [
            'rootMessages' => $rootMessages,
            'lastReplies' => $lastReplies,
            'pets' => $pets
        ]);
    }
}
