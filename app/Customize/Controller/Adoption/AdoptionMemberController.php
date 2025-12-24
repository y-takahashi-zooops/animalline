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
use Customize\Entity\ConservationBankAccount;
use Customize\Form\Type\Adoption\ConservationBankAccountType;
use Customize\Repository\ConservationBankAccountRepository;
use Eccube\Form\Type\Front\EntryType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Customize\Repository\ConservationContactHeaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Eccube\Common\EccubeConfig;

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
     * @var UserPasswordHasherInterface
     */
    protected $passwordHasher;

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
     * @var ConservationBankAccountRepository
     */
    protected $conservationBankAccountRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ConservationController constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param ConservationsRepository $conservationsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param UserPasswordHasherInterface $passwordHasher
     * @param TokenStorageInterface $tokenStorage
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param BenefitsStatusRepository $benefitsStatusRepository
     * @param ConservationBankAccountRepository $conservationBankAccountRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerRepository  $customerRepository,
        ConservationsRepository  $conservationsRepository,
        AdoptionQueryService  $adoptionQueryService,
        UserPasswordHasherInterface $passwordHasher,
        TokenStorageInterface $tokenStorage,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        BenefitsStatusRepository $benefitsStatusRepository,
        ConservationBankAccountRepository $conservationBankAccountRepository,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        EccubeConfig $eccubeConfig,
    ) {
        $this->customerRepository = $customerRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->passwordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->benefitsStatusRepository = $benefitsStatusRepository;
        $this->conservationBankAccountRepository = $conservationBankAccountRepository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
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
            $this->logger->info('認証済のためログイン処理をスキップ');

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
            $conservation->setViewCount(0);
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

            $entityManager = $this->entityManager;
            $entityManager->persist($conservation);

            if($conservation->getIdHash() == ""){
                $conservation->setIdHash(md5($conservation->getId()));
                $entityManager->persist($conservation);
            }

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
            'plain_password' => $request->get('entry')['password']['first'] ?? '',
            'email' => $request->get('entry')['email']['first'] ?? '',
        ]);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Customer' => $customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_INITIALIZE);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('会員編集開始');

            if ($customer->getPassword() === $this->eccubeConfig['eccube_default_password']) {
                $customer->setPassword($previousPassword);
            } else {
                $encoder = $this->passwordHasher->getEncoder($customer);
                if ($customer->getSalt() === null) {
                    $customer->setSalt($encoder->createSalt());
                }
                $customer->setPassword(
                    $encoder->encodePassword($customer->getPassword(), $customer->getSalt())
                );
            }
            $this->entityManager->flush();

            $this->logger->info('会員編集完了');

            $event = new EventArgs(
                [
                    'form' => $form,
                    'Customer' => $customer,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_COMPLETE);

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

    /**
     * 口座登録画面.
     *
     * @Route("/adoption/member/bank/regist", name="adoption_bank_regist")
     * @Template("animalline/adoption/member/bank_regist.twig")
     */
    public function bank_regist(Request $request)
    {
        $user = $this->getUser();
        $conservation = $this->conservationsRepository->find($user);
        $BankAccount = $this->conservationBankAccountRepository->findOneBy(['Conservation' => $conservation]);
        if (!$BankAccount) {
            $BankAccount = new ConservationBankAccount();
        }

        $form = $this->createForm(ConservationBankAccountType::class, $BankAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':

                    return $this->render('animalline/adoption/member/bank_regist_confirm.twig', [
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

                    $data->setConservation($conservation);
                    $this->entityManager->persist($data);
                    $this->entityManager->flush();

                    return $this->redirect($this->generateUrl('adoption_bank_regist_complete'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 口座登録完了.
     *
     * @Route("/adoption/member/bank/regist/complete", name="adoption_bank_regist_complete")
     * @Template("animalline/adoption/member/bank_regist_complete.twig")
     */
    public function bank_regist_complete()
    {
        return [];
    }
}
