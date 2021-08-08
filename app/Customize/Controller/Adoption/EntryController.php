<?php
//保護団体会員登録用コントローラー

namespace Customize\Controller\Adoption;

use Customize\Form\Type\AdoptionEntryType;
use Customize\Form\Type\AdoptionLoginType;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\Master\CustomerStatusRepository;
use Customize\Service\MailService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Customize\Entity\Conservations;
use Customize\Repository\ConservationsRepository;
use Customize\Config\AnilineConf;

class EntryController extends AbstractController
{
    /**
     * @var CustomerStatusRepository
     */
    protected $customerStatusRepository;

    /**
     * @var ValidatorInterface
     */
    protected $recursiveValidator;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * EntryController constructor.
     *
     * @param CustomerStatusRepository $customerStatusRepository
     * @param MailService $mailService
     * @param BaseInfoRepository $baseInfoRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param ValidatorInterface $validatorInterface
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param ConservationsRepository $conservationsRepository
     */
    public function __construct(
        CustomerStatusRepository $customerStatusRepository,
        MailService $mailService,
        BaseInfoRepository $baseInfoRepository,
        EncoderFactoryInterface $encoderFactory,
        ValidatorInterface $validatorInterface,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        ConservationsRepository $conservationsRepository
    ) {
        $this->customerStatusRepository = $customerStatusRepository;
        $this->mailService = $mailService;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->encoderFactory = $encoderFactory;
        $this->recursiveValidator = $validatorInterface;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->conservationsRepository = $conservationsRepository;
    }

    /**
     * 会員登録画面.
     *
     * @Route("/adoption/configration/entry", name="adoption_entry")
     * @Template("animalline/adoption/entry/index.twig")
     */
    public function index(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('adoption_mypage');
        }

        /* @var $Conservations \Customize\Entity\Conservations */
        $Conservation = $this->conservationsRepository->newConservation();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $form = $this->createForm(AdoptionEntryType::class);

         /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
         $builder = $this->formFactory->createBuilder(AdoptionEntryType::class, $Conservation);

         $event = new EventArgs(
             [
                 'builder' => $builder,
                 'Customer' => $Conservation,
             ],
             $request
         );
         $this->eventDispatcher->dispatch(AnilineConf::ANILINE_ADOPTION_ENTRY_INDEX_INITIALIZE, $event);
 
         /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
    
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $encoder = $this->encoderFactory->getEncoder($Conservation);
            $salt = $encoder->createSalt();
            $password = $encoder->encodePassword($Conservation->getPassword(), $salt);
            $secretKey = $this->conservationsRepository->getUniqueSecretKey();

            $Conservation
                ->setSalt($salt)
                ->setPassword($password)
                ->setSecretKey($secretKey)
                ->setEmail($formData['email']);

            $this->entityManager->persist($Conservation);
            $this->entityManager->flush();

            log_info('会員登録完了');

            $activateUrl = $this->generateUrl('adoption_entry_activate', ['secret_key' => $Conservation->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);

            // メール送信
            $this->mailService->sendCustomerConfirmMail($Conservation, $activateUrl);

            log_info('仮会員登録完了画面へリダイレクト');

            return $this->redirectToRoute('adoption_entry_complete');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 会員登録完了画面.
     *
     * @Route("/adoption/configration/entry/complete", name="adoption_entry_complete")
     * @Template("animalline/adoption/entry/complete.twig")
     */
    public function complete()
    {
        return [];
    }

    /**
     * 会員のアクティベート（本会員化）を行う.
     *
     * @Route("/adoption/configration/entry/activate/{secret_key}", name="adoption_entry_activate")
     * @Template("animalline/adoption/entry/activate.twig")
     */
    public function activate(Request $request, $secret_key)
    {
        $errors = $this->recursiveValidator->validate(
            $secret_key,
            [
                new Assert\NotBlank(),
                new Assert\Regex(
                    [
                        'pattern' => '/^[a-zA-Z0-9]+$/',
                    ]
                ),
            ]
        );

        if ($request->getMethod() === 'GET' && count($errors) === 0) {

            // 会員登録処理を行う
            $qtyInCart = $this->entryActivate($request, $secret_key);

            return [
                'qtyInCart' => $qtyInCart,
            ];
        }

        throw new HttpException\NotFoundHttpException();
    }

    /**
     * ログイン画面.
     *
     * @Route("/adoption/configration/login", name="adoption_login")
     * @Template("animalline/adoption/configration/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('adoption_mypage');
        }

        log_info('login処理開始');

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory
            ->createNamedBuilder('', AdoptionLoginType::class);
        $builder->get('login_memory')->setData((bool) $request->getSession()->get('_security.adoption_login_memory'));

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Conservation = $this->getUser();
            if ($Conservation instanceof Conservations) {
                $builder->get('email')
                    ->setData($Conservation->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );

        $form = $builder->getForm();

        $this->eventDispatcher->dispatch(AnilineConf::ANILINE_ADOPTION_LOGIN_INITIALIZE, $event);

        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];

        log_info('処理終了');
    }

    /**
     * 会員登録処理を行う
     *
     * @param Request $request
     * @param $secret_key
     */
    private function entryActivate(Request $request, $secret_key)
    {
        log_info('本会員登録開始');

        $Conservation = $this->conservationsRepository->findOneBy(['secret_key' => $secret_key]);
        if (is_null($Conservation)) {
            throw new HttpException\NotFoundHttpException();
        }

        $CustomerStatus = $this->customerStatusRepository->find(CustomerStatus::REGULAR);
        $Status = $Conservation->getRegisterStatusId();

        // すでに会員の場合は何もしない
        if ($Status == $CustomerStatus) {
            return 0;
        }
        $Conservation->setRegisterStatusId($CustomerStatus);
        $this->entityManager->persist($Conservation);
        $this->entityManager->flush();

        log_info('本会員登録完了');

        $event = new EventArgs(
            [
                'Conservation' => $Conservation,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(AnilineConf::ANILINE_ADOPTION_LOGIN_COMPLETE, $event);

        // メール送信
        $this->mailService->sendCustomerCompleteMail($Conservation);

        // 本会員登録してログイン状態にする
        $token = new UsernamePasswordToken($Conservation->getEmail(), null, 'adoption', ['ROLE_ADOPTION_USER']);
        $this->tokenStorage->setToken($token);
        $request->getSession()->migrate(true);

        // log_info('ログイン済に変更', [$this->getUser()->getId()]);

        return 0;
    }
}
