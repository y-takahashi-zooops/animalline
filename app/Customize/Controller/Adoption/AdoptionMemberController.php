<?php

namespace Customize\Controller\Adoption;

use Customize\Repository\BenefitsStatusRepository;
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
use Eccube\Form\Type\Front\EntryType;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Customize\Repository\ConservationContactHeaderRepository;

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
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * ConservationController constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param ConservationsRepository $conservationsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param EncoderFactoryInterface $encoderFactory
     * @param TokenStorageInterface $tokenStorage
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param BenefitsStatusRepository $benefitsStatusRepository
     */
    public function __construct(
        CustomerRepository  $customerRepository,
        ConservationsRepository  $conservationsRepository,
        AdoptionQueryService  $adoptionQueryService,
        EncoderFactoryInterface $encoderFactory,
        TokenStorageInterface $tokenStorage,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        BenefitsStatusRepository $benefitsStatusRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->benefitsStatusRepository = $benefitsStatusRepository;
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
        $canBenefits = false;
        $contactHeaders = $this->conservationContactHeaderRepository->findBy(["Customer" => $user, "contract_status" => AnilineConf::CONTRACT_STATUS_CONTRACT]);

        foreach ($contactHeaders as $contactHeader) {
            $canBenefits = !$this->benefitsStatusRepository->findOneBy(['site_type' => AnilineConf::SITE_CATEGORY_CONSERVATION, 'pet_id' => $contactHeader->getPet()->getId()]);
        }

        $pets = $this->adoptionQueryService->findAdoptionFavoritePets($this->getUser()->getId());

        $customer_newmsg = 0;
        if($this->conservationContactHeaderRepository->findBy(["Customer" => $user, "customer_new_msg" => 1])){
            $customer_newmsg = 1;
        }

        $conservation_newmsg = 0;
        if($this->conservationContactHeaderRepository->findBy(["Conservation" => $user, "conservation_new_msg" => 1])){
            $conservation_newmsg = 1;
        }
        
        return $this->render('animalline/adoption/member/index.twig', [
            'conservation' => $conservation,
            'pets' => $pets,
            'user' => $this->getUser(),
            'customer_newmsg' => $customer_newmsg,
            'conservation_newmsg' => $conservation_newmsg,
            'canBenefits' => $canBenefits
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
        $license_thumbnail_path = $request->get('license_thumbnail_path') ?: $conservation->getLicenseThumbnailPath();

        $builder = $this->formFactory->createBuilder(ConservationsType::class, $conservation,array(
            'adoption_img' => $thumbnail_path,
            'license_img' => $license_thumbnail_path,
        ));

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
                ->setThumbnailPath($thumbnail_path)
                ->setLicenseThumbnailPath($license_thumbnail_path);

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
            'thumbnail_path' => $thumbnail_path,
            'license_thumbnail_path' => $license_thumbnail_path
        ];
    }

    /**
     * 会員情報編集画面.
     *
     * @Route("/adoption/member/change", name="adoption_change")
     * @Template("animalline/adoption/member/adoption_change.twig")
     */
    public function adoption_change(Request $request)
    {
        $customer = $this->getUser();
        $loginCustomer = clone $customer;
        $this->entityManager->detach($loginCustomer);

        $previousPassword = $customer->getPassword();
        $customer->setPassword($this->eccubeConfig['eccube_default_password']);

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createBuilder(EntryType::class, $customer, [
            'password' => $request->get('entry')['password']['first'] ?? '',
            'email' => $request->get('entry')['email']['first'] ?? '',
        ]);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Customer' => $customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('会員編集開始');

            if ($customer->getPassword() === $this->eccubeConfig['eccube_default_password']) {
                $customer->setPassword($previousPassword);
            } else {
                $encoder = $this->encoderFactory->getEncoder($customer);
                if ($customer->getSalt() === null) {
                    $customer->setSalt($encoder->createSalt());
                }
                $customer->setPassword(
                    $encoder->encodePassword($customer->getPassword(), $customer->getSalt())
                );
            }
            $this->entityManager->flush();

            log_info('会員編集完了');

            $event = new EventArgs(
                [
                    'form' => $form,
                    'Customer' => $customer,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_COMPLETE, $event);

            return $this->redirect($this->generateUrl('adoption_change_complete'));
        }

        $this->tokenStorage->getToken()->setUser($loginCustomer);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 会員情報編集完了画面.
     *
     * @Route("/adoption/member/adoption_change_complete", name="adoption_change_complete")
     * @Template("animalline/adoption/member/adoption_change_complete.twig")
     */
    public function complete(Request $request)
    {
        return [];
    }
}
