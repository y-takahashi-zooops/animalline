<?php

namespace Customize\Controller\Breeder;

use Customize\Entity\BreederPetinfoTemplate;
use Customize\Form\Type\Breeder\BreederPetinfoTemplateType;
use Customize\Repository\BenefitsStatusRepository;
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
use Customize\Entity\BankAccount;
use Customize\Form\Type\Breeder\BankAccountType;
use Customize\Form\Type\Front\ResetPasswordType;
use Customize\Repository\BankAccountRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Eccube\Form\Type\Front\EntryType;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Service\MailService;
use Customize\Entity\BreederContactHeader;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Eccube\Common\EccubeConfig;

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
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var UserPasswordHasherInterface
     */
    protected $passwordHasher;

    /**
     * @var BankAccountRepository
     */
    protected $bankAccountRepository;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * BreederController constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param BreedersRepository $breedersRepository
     * @param BreederQueryService $breederQueryService
     * @param UserPasswordHasherInterface $passwordHasher
     * @param TokenStorageInterface $tokenStorage
     * @param BankAccountRepository $bankAccountRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param BenefitsStatusRepository $benefitsStatusRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param MailService $mailService
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     * @param EventDispatcherInterface $eventDispatcher;
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepository  $customerRepository,
        BreedersRepository  $breedersRepository,
        BreederQueryService $breederQueryService,
        UserPasswordHasherInterface $passwordHasher,
        TokenStorageInterface $tokenStorage,
        BankAccountRepository $bankAccountRepository,
        BreederContactHeaderRepository $breederContactHeaderRepository,
        BenefitsStatusRepository $benefitsStatusRepository,
        BreederPetsRepository  $breederPetsRepository,
        MailService $mailService,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        EccubeConfig $eccubeConfig
    ) {
        $this->customerRepository = $customerRepository;
        $this->breedersRepository = $breedersRepository;
        $this->breederQueryService = $breederQueryService;
        $this->passwordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->benefitsStatusRepository = $benefitsStatusRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->eccubeConfig = $eccubeConfig;
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
            $this->logger->info('認証済のためログイン処理をスキップ');

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
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_MYPAGE_MYPAGE_LOGIN_INITIALIZE);

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
        $canBenefits = false;

        //問い合わせから来た場明の判定とセッション変数取得
        $contact_save = $request->cookies->get('contact_save');
        if($contact_save){
            $contact_pet_id = $request->cookies->get('contact_pet');
            $contact_image = $request->cookies->get('contact_image');
            $contact_title = $request->cookies->get('contact_title');
            $contact_description = $request->cookies->get('contact_description');
            $booking_request = $request->cookies->get('booking_request');
            $contact_type = $request->cookies->get('contact_type');

            $contact = new BreederContactHeader();
            $pet = $this->breederPetsRepository->find($contact_pet_id);

            $contact
                    ->setSendDate(Carbon::now())
                    ->setPet($pet)
                    ->setBreeder($pet->getBreeder())
                    ->setCustomer($this->getUser())
                    ->setContactTitle($contact_title)
                    ->setImageFile($contact_image)
                    ->setContactDescription($contact_description)
                    ->setContactType($contact_type)
                    ->setLastMessageDate(Carbon::now());

	    $this->entityManager->persist($contact);

            $pet->setIsContact(1);
            $this->entityManager->persist($pet);

            $this->entityManager->flush();

	    $breeder = $this->customerRepository->find($contact->getBreeder()->getId());
            $this->mailService->sendMailContractAccept($breeder, 1);

            return $this->redirectToRoute('breeder_contact_complete', ['pet_id' => $contact_pet_id]);
        }
        //ここまで

        $pets = $this->breederQueryService->findBreederFavoritePets($this->getUser()->getId());

        $customer_newmsg = 0;
        if ($this->breederContactHeaderRepository->findBy(["Customer" => $user, "customer_new_msg" => 1])) {
            $customer_newmsg = 1;
        }

        $breeder_newmsg = 0;
        if ($this->breederContactHeaderRepository->findBy(["Breeder" => $user, "breeder_new_msg" => 1])) {
            $breeder_newmsg = 1;
        }

        $contactHeaders = $this->breederContactHeaderRepository->findBy(["Customer" => $user, "contract_status" => AnilineConf::CONTRACT_STATUS_CONTRACT]);

        foreach ($contactHeaders as $contactHeader) {
            $canBenefits = !$this->benefitsStatusRepository->findOneBy(['site_type' => AnilineConf::SITE_CATEGORY_BREEDER, 'pet_id' => $contactHeader->getPet()->getId()]);
        }

        return $this->render('animalline/breeder/member/index.twig', [
            'breeder' => $breeder,
            'pets' => $pets,
            'user' => $this->getUser(),
            'customer_newmsg' => $customer_newmsg,
            'breeder_newmsg' => $breeder_newmsg,
            'canBenefits' => $canBenefits
        ]);
    }

    /**
     *
     * サイトサンプル
     *
     * @Route("/breeder/member/sample", name="breeder_site_sample_menu")
     * @Template("animalline/breeder/member/site_sample_index.twig")
     */
    public function breeder_site_sample_menu(Request $request)
    {
        return [];
    }

    /**
     *
     * サイトサンプル
     *
     * @Route("/breeder/member/sample/image/{image_name}", name="breeder_site_sample_image")
     * @Template("animalline/breeder/member/site_sample_image.twig")
     */
    public function breeder_site_sample_image(Request $request, $image_name)
    {
        return ["image_name" => $image_name];
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
            $breederData->setViewCount(0);
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
	    $entityManager = $this->entityManager;
	    $entityManager->persist($breederData);

            if($breederData->getIdHash() == ""){
                $breederData->setIdHash(md5($breederData->getId()));
                $entityManager->persist($breederData);
            }

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
            $entityManager = $this->entityManager;
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
	$entityManager = $this->entityManager;

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

    /**
     * 会員情報編集画面.
     *
     * @Route("/breeder/member/change", name="breeder_change")
     * @Template("animalline/breeder/member/breeder_change.twig")
     */
    public function breeder_change(Request $request)
    {
        $Customer = $this->getUser();
        $LoginCustomer = clone $Customer;
        $this->entityManager->detach($LoginCustomer);

        $previous_password = $Customer->getPassword();
        $Customer->setPassword($this->eccubeConfig['eccube_default_password']);

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createBuilder(EntryType::class, $Customer, [
            'plain_password' => $request->get('entry')['password']['first'] ?? '',
            'email' => $request->get('entry')['email']['first'] ?? '',
        ]);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_INITIALIZE);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('会員編集開始');

            if ($Customer->getPassword() === $this->eccubeConfig['eccube_default_password']) {
                $Customer->setPassword($previous_password);
            } else {
                $encoder = $this->passwordHasher->getEncoder($Customer);
                if ($Customer->getSalt() === null) {
                    $Customer->setSalt($encoder->createSalt());
                }
                $Customer->setPassword(
                    $encoder->encodePassword($Customer->getPassword(), $Customer->getSalt())
                );
            }
            $this->entityManager->flush();

            $this->logger->info('会員編集完了');

            $event = new EventArgs(
                [
                    'form' => $form,
                    'Customer' => $Customer,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_COMPLETE);

            return $this->redirect($this->generateUrl('breeder_change_complete'));
        }

        $this->tokenStorage->getToken()->setUser($LoginCustomer);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 会員情報編集完了画面.
     *
     * @Route("/breeder/member/breeder_change_complete", name="breeder_change_complete")
     * @Template("animalline/breeder/member/breeder_change_complete.twig")
     */
    public function complete(Request $request)
    {
        return [];
    }

    /**
     * 口座登録画面.
     *
     * @Route("/breeder/member/bank/regist", name="breeder_bank_regist")
     * @Template("animalline/breeder/member/bank_regist.twig")
     */
    public function bank_regist(Request $request)
    {
        $user = $this->getUser();
        $breeder = $this->breedersRepository->find($user);
        $BankAccount = $this->bankAccountRepository->findOneBy(['Breeder' => $breeder]);
        if (!$BankAccount) {
            $BankAccount = new BankAccount();
        }

        $form = $this->createForm(BankAccountType::class, $BankAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':

                    return $this->render('animalline/breeder/member/bank_regist_confirm.twig', [
                        'form' => $form->createView(),
                    ]);

                case 'complete':

                    $data = $form->getData();

                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'data' => $data,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_CONTACT_INDEX_COMPLETE);

                    $data = $event->getArgument('data');

                    $data->setBreeder($breeder);
                    $this->entityManager->persist($data);
                    $this->entityManager->flush();

                    return $this->redirect($this->generateUrl('breeder_bank_regist_complete'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 口座登録完了.
     *
     * @Route("/breeder/member/bank/regist/complete", name="breeder_bank_regist_complete")
     * @Template("animalline/breeder/member/bank_regist_complete.twig")
     */
    public function bank_regist_complete()
    {
        return [];
    }
}
