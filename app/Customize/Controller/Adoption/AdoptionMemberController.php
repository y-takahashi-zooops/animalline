<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPetImage;
use Customize\Entity\ConservationPets;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\AdoptionQueryService;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Adoption\ConservationsType;
use Customize\Form\Type\Adoption\ConservationPetsType;
use Customize\Form\Type\Adoption\ConservationHouseType;
use Customize\Entity\Conservations;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationsHouse;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\ConservationPetImageRepository;

use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Adoption\ConservationContactType;
use DateTime;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;

class AdoptionMemberController extends AbstractController
{
    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

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
     * @var ConservationsHousesRepository
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
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * ConservationController constructor.
     *
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param ConservationsRepository $conservationsRepository
     * @param PrefRepository $prefRepository
     * @param ConservationsHousesRepository $conservationsHouseRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param CustomerRepository $customerRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaQueryService $dnaQueryService
     */
    public function __construct(
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        ConservationContactsRepository      $conservationContactsRepository,
        AdoptionQueryService                $adoptionQueryService,
        PetsFavoriteRepository              $petsFavoriteRepository,
        SendoffReasonRepository             $sendoffReasonRepository,
        ConservationsRepository             $conservationsRepository,
        PrefRepository                      $prefRepository,
        ConservationsHousesRepository       $conservationsHouseRepository,
        ConservationPetsRepository          $conservationPetsRepository,
        CustomerRepository                  $customerRepository,
        ConservationPetImageRepository      $conservationPetImageRepository,
        DnaCheckStatusRepository            $dnaCheckStatusRepository,
        DnaQueryService                     $dnaQueryService
    )
    {
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->prefRepository = $prefRepository;
        $this->conservationsHouseRepository = $conservationsHouseRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->customerRepository = $customerRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaQueryService = $dnaQueryService;
    }

    /**
     * ログイン画面.
     *
     * @Route("/adoption/login", name="adoption_login")
     * @Template("animalline/adoption/login.twig")
     */
    public function adoption_login(Request $request, AuthenticationUtils $utils)
    {
        //ログイン完了後に元のページに戻るためのセッション変数を設定
        $referer = $request->headers->get('referer');
        /*
        if($referer){
            $referers = parse_url($referer);
            if($referers['host'] == $request->getHost()) {
                $this->setLoginTargetPath($referer);
            }
        }
        */
        //ログイン完了後に元のページに戻るためのセッション変数を設定

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('adoption_mypage');
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory
            ->createNamedBuilder('', CustomerLoginType::class);

        $builder->get('login_memory')->setData((bool)$request->getSession()->get('_security.login_memory'));

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Customer = $this->getUser();
            if ($Customer instanceof Customer) {
                $builder->get('login_email')
                    ->setData($Customer->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_LOGIN_INITIALIZE, $event);

        $form = $builder->getForm();

        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
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
        $user = $this->getUser();
        $conservation = $this->conservationsRepository->find($user);

        $pets = $this->adoptionQueryService->findAdoptionFavoritePets($this->getUser()->getId());

        return $this->render('animalline/adoption/member/index.twig', [
            'conservation' => $conservation,
            'pets' => $pets,
            'user' => $this->getUser(),
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
        if (!$conservation) {
            $conservation = new Conservations;
            $conservation->setId($user->getId());
        }
        $builder = $this->formFactory->createBuilder(ConservationsType::class, $conservation);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnail_path = $request->get('thumbnail_path') ?: $conservation->getThumbnailPath();

            $conservation->setPref($conservation->getPrefId())
                ->setId($user->getId())
                ->setThumbnailPath($thumbnail_path);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();
            return $this->redirectToRoute('adoption_examination');
        } elseif (!$form->isSubmitted() && !$conservationsRepository->find($user)) {
            // Customer情報から初期情報をセット
            $Customer = $this->customerRepository->find($user);
            $form->get('owner_name')->setData($Customer->getname01() . "　" . $Customer->getname02());
            $form->get('owner_kana')->setData($Customer->getkana01() . "　" . $Customer->getkana02());
            $form->get('zip')->setData($Customer->getPostalCode());
            $form->get('PrefId')->setData($Customer->getPref());
            $form->get('city')->setData($Customer->getAddr01());
            $form->get('address')->setData($Customer->getAddr02());
            $form->get('tel')->setData($Customer->getPhoneNumber());
        }

        return [
            'conservation' => $conservation,
            'form' => $form->createView()
        ];
    }
}
