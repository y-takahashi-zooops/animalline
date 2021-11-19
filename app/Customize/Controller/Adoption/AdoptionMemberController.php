<?php

namespace Customize\Controller\Adoption;

use Customize\Service\AdoptionQueryService;
use Eccube\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Adoption\ConservationsType;
use Customize\Entity\Conservations;
use Customize\Repository\ConservationsRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;
use Customize\Config\AnilineConf;

class AdoptionMemberController extends AbstractController
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * ConservationController constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param ConservationsRepository $conservationsRepository
     * @param AdoptionQueryService $adoptionQueryService
     */
    public function __construct(
        CustomerRepository  $customerRepository,
        ConservationsRepository  $conservationsRepository,
        AdoptionQueryService  $adoptionQueryService
    ) {
        $this->customerRepository = $customerRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
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
    public function base_info(Request $request, ConservationsRepository $conservationsRepository)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if ($return_path == "") {
            $return_path = "adoption_examination";
        }

        $user = $this->getUser();

        $conservation = $conservationsRepository->find($user);
        if (!$conservation) {
            $conservation = new Conservations;
            $conservation->setId($user->getId());
            $conservation->setExaminationStatus(0);
        }

        $thumbnail_path = $request->get('thumbnail_path') ?: $conservation->getThumbnailPath();

        $builder = $this->formFactory->createBuilder(ConservationsType::class, $conservation);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $handling_pet_kind = $form->getData()->getHandlingPetKind();

            if ($handling_pet_kind == AnilineConf::ANILINE_PET_KIND_DOG) {
                $conservation->setConservationHouseNameCat(null);
            } elseif ($handling_pet_kind == AnilineConf::ANILINE_PET_KIND_CAT) {
                $conservation->setConservationHouseNameDog(null);
            }
            $conservation->setPref($conservation->getPrefId())
                ->setId($user->getId())
                ->setThumbnailPath($thumbnail_path);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();
            return $this->redirectToRoute($return_path);
        }

        return [
            'return_path' => $return_path,
            'conservation' => $conservation,
            'form' => $form->createView(),
            'Customer' => $user,
            'thumbnail_path' => $thumbnail_path
        ];
    }
}
