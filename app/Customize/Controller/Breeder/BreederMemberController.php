<?php

namespace Customize\Controller\Breeder;

use Customize\Entity\BreederPetinfoTemplate;
use Customize\Form\Type\Breeder\BreederPetinfoTemplateType;
use Customize\Service\BreederQueryService;
use Eccube\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Breeder\BreedersType;
use Customize\Entity\Breeders;
use Customize\Repository\BreedersRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;
use Customize\Config\AnilineConf;
use Customize\Form\Type\Front\ResetPasswordType;

class BreederMemberController extends AbstractController
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * BreederController constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param BreedersRepository $breedersRepository
     * @param BreederQueryService $breederQueryService
     */
    public function __construct(
        CustomerRepository  $customerRepository,
        BreedersRepository  $breedersRepository,
        BreederQueryService $breederQueryService
    ) {
        $this->customerRepository = $customerRepository;
        $this->breedersRepository = $breedersRepository;
        $this->breederQueryService = $breederQueryService;
    }

    /**
     * ログイン画面.
     *
     * @Route("/breeder/login", name="breeder_login")
     * @Template("animalline/breeder/login.twig")
     */
    public function breeder_login(Request $request, AuthenticationUtils $utils)
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

            return $this->redirectToRoute('breeder_mypage');
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
     * @Route("/breeder/member/", name="breeder_mypage")
     * @Template("animalline/breeder/member/index.twig")
     */
    public function breeder_mypage(Request $request)
    {
        $user = $this->getUser();
        $breeder = $this->breedersRepository->find($user);

        $pets = $this->breederQueryService->findBreederFavoritePets($this->getUser()->getId());

        return $this->render('animalline/breeder/member/index.twig', [
            'breeder' => $breeder,
            'pets' => $pets,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * 基本情報編集画面
     *
     * @Route("/breeder/member/baseinfo", name="breeder_baseinfo")
     * @Template("/animalline/breeder/member/base_info.twig")
     */
    public function base_info(Request $request, BreedersRepository $breedersRepository)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if ($return_path == "") {
            $return_path = "breeder_examination";
        }

        $user = $this->getUser();

        $breederData = $breedersRepository->find($user);
        if (!$breederData) {
            $breederData = new Breeders;
            $breederData->setId($user->getId());
        }

        $thumbnail_path = $request->get('thumbnail_path') ?: $breederData->getThumbnailPath();
        $license_thumbnail_path = $request->get('license_thumbnail_path') ?: $breederData->getLicenseThumbnailPath();

        $builder = $this->formFactory->createBuilder(BreedersType::class, $breederData, array(
            'breeder_img' => $thumbnail_path,
            'license_img' => $license_thumbnail_path,
        ));
        $form = $builder->getForm();
        $form->handleRequest($request);

        $breederPetinfoTemplate = $breederData->getBreederPetinfoTemplate() ?: new BreederPetinfoTemplate();
        $builderTemplate = $this->formFactory->createBuilder(BreederPetinfoTemplateType::class, $breederPetinfoTemplate);
        $formTemplate = $builderTemplate->getForm();
        $formTemplate->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $formTemplate->isSubmitted() && $formTemplate->isValid()) {
            $handling_pet_kind = $form->getData()->getHandlingPetKind();

            if ($handling_pet_kind == AnilineConf::ANILINE_PET_KIND_DOG) {
                $breederData->setBreederHouseNameCat(null);
            } elseif ($handling_pet_kind == AnilineConf::ANILINE_PET_KIND_CAT) {
                $breederData->setBreederHouseNameDog(null);
            }

            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense())
                ->setThumbnailPath($thumbnail_path)
                ->setLicenseThumbnailPath($license_thumbnail_path);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $breederPetinfoTemplate->setBreeder($breederData);
            $entityManager->persist($breederPetinfoTemplate);
            $entityManager->flush();
            return $this->redirectToRoute($return_path);
        }

        return [
            'return_path' => $return_path,
            'breederData' => $breederData,
            'form' => $form->createView(),
            'formTemplate' => $formTemplate->createView(),
            'Customer' => $user,
            'thumbnail_path' => $thumbnail_path,
            'license_thumbnail_path' => $license_thumbnail_path
        ];
    }

    /**
     * テンプレート編集画面
     *
     * @Route("/breeder/member/template", name="breeder_template")
     * @Template("/animalline/breeder/member/template.twig")
     */
    public function template(Request $request)
    {
        $breeder = $this->breedersRepository->find($this->getUser());
        if (!$breeder) {
            return $this->redirectToRoute('breeder_mypage');
        }
        $breederPetinfoTemplate = $breeder->getBreederPetinfoTemplate() ?: new BreederPetinfoTemplate();
        $builder = $this->formFactory->createBuilder(BreederPetinfoTemplateType::class, $breederPetinfoTemplate);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $breederPetinfoTemplate->setBreeder($breeder);
            $entityManager->persist($breederPetinfoTemplate);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_mypage');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * パスワードリセット.
     *
     * @Route("/breeder/member/set_password", name="password_change")
     * @Template("/animalline/breeder/member/password_change.twig")
     */
    public function password_change(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $builder = $this->formFactory
            ->createBuilder(ResetPasswordType::class);

        $form = $builder->getForm();
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $userEntity = $this->customerRepository->find($user);
            $password = $form->get('password')->getData();

            $pass = $encoder->encodePassword($user, $password);
            $userEntity->setPassword($pass);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_mypage');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
