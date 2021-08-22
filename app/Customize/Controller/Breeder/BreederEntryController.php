<?php
//保護団体会員登録用コントローラー

namespace Customize\Controller\Breeder;

use Customize\Form\Type\Breeder\BreederEntryType;
use Customize\Form\Type\Breeder\BreederLoginType;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Repository\BaseInfoRepository;
use Customize\Service\BreederMailService;
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
use Customize\Entity\Breeders;
use Customize\Repository\BreedersRepository;
use Customize\Config\AnilineConf;

class BreederEntryController extends AbstractController
{
    /**
     * @var ValidatorInterface
     */
    protected $recursiveValidator;

    /**
     * @var BreederMailService
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
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * EntryController constructor.
     *
     * @param BreederMailService $mailService
     * @param BaseInfoRepository $baseInfoRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param ValidatorInterface $validatorInterface
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreederMailService $mailService,
        BaseInfoRepository $baseInfoRepository,
        EncoderFactoryInterface $encoderFactory,
        ValidatorInterface $validatorInterface,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        BreedersRepository $breedersRepository
    ) {
        $this->mailService = $mailService;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->encoderFactory = $encoderFactory;
        $this->recursiveValidator = $validatorInterface;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * 会員登録画面.
     *
     * @Route("/breeder/configration/entry", name="breeder_entry")
     * @Template("animalline/breeder/configration/entry/index.twig")
     */
    public function index(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('breeder_configration');
        }

        /* @var $Breeders \Customize\Entity\Breeders */
        $Breeder = $this->breedersRepository->newBreeder();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $form = $this->createForm(BreederEntryType::class);

         /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
         $builder = $this->formFactory->createBuilder(BreederEntryType::class, $Breeder);

         $event = new EventArgs(
             [
                 'builder' => $builder,
                 'Customer' => $Breeder,
             ],
             $request
         );
         $this->eventDispatcher->dispatch(AnilineConf::ANILINE_BREEDER_ENTRY_INDEX_INITIALIZE, $event);
 
         /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
    
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $encoder = $this->encoderFactory->getEncoder($Breeder);
            $salt = $encoder->createSalt();
            $password = $encoder->encodePassword($Breeder->getPassword(), $salt);
            $secretKey = $this->breedersRepository->getUniqueSecretKey();

            $Breeder
                ->setSalt($salt)
                ->setPassword($password)
                ->setSecretKey($secretKey)
                ->setEmail($formData->getEmail());

            $this->entityManager->persist($Breeder);
            $this->entityManager->flush();

            log_info('会員登録完了');

            $activateUrl = $this->generateUrl('breeder_entry_activate', ['secret_key' => $Breeder->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);

            // メール送信
            $this->mailService->sendCustomerConfirmMail($Breeder, $activateUrl);

            log_info('仮会員登録完了画面へリダイレクト');

            return $this->redirectToRoute('breeder_entry_complete');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 会員登録完了画面.
     *
     * @Route("/breeder/configration/entry/complete", name="breeder_entry_complete")
     * @Template("animalline/breeder/configration/entry/complete.twig")
     */
    public function complete()
    {
        return [];
    }

    /**
     * 会員のアクティベート（本会員化）を行う.
     *
     * @Route("/breeder/configration/entry/activate/{secret_key}", name="breeder_entry_activate")
     * @Template("animalline/breeder/configration/entry/activate.twig")
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
     * @Route("/breeder/configration/login", name="breeder_login")
     * @Template("animalline/breeder/configration/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('breeder_configration');
        }

        log_info('login処理開始');

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory
            ->createNamedBuilder('', BreederLoginType::class);
        $builder->get('login_memory')->setData((bool) $request->getSession()->get('_security.breeder_login_memory'));

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Breeder = $this->getUser();
            if ($Breeder instanceof Breeders) {
                $builder->get('email')
                    ->setData($Breeder->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );

        $form = $builder->getForm();

        $this->eventDispatcher->dispatch(AnilineConf::ANILINE_BREEDER_LOGIN_INITIALIZE, $event);

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

        $Breeder = $this->breedersRepository->findOneBy(['secret_key' => $secret_key]);
        if (is_null($Breeder)) {
            throw new HttpException\NotFoundHttpException();
        }

        $register_status_id = $Breeder->getRegisterStatusId();

        // すでに会員の場合は何もしない
        if ($register_status_id == AnilineConf::ANILINE_REGISTER_STATUS_ACTIVE) {
            return 0;
        }
        $Breeder->setRegisterStatusId(AnilineConf::ANILINE_REGISTER_STATUS_ACTIVE);
        $this->entityManager->persist($Breeder);
        $this->entityManager->flush();

        log_info('本会員登録完了');

        $event = new EventArgs(
            [
                'Breeder' => $Breeder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(AnilineConf::ANILINE_BREEDER_LOGIN_COMPLETE, $event);

        // メール送信
        $this->mailService->sendCustomerCompleteMail($Breeder);

        // 本会員登録してログイン状態にする
        $token = new UsernamePasswordToken($Breeder->getEmail(), null, 'breeder', ['ROLE_BREEDER_USER']);
        $this->tokenStorage->setToken($token);
        $request->getSession()->migrate(true);

        // log_info('ログイン済に変更', [$this->getUser()->getId()]);

        return 0;
    }
}
