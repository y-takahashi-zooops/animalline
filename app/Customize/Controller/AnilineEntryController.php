<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Front\EntryType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\ConservationsRepository;
use Eccube\Repository\Master\CustomerStatusRepository;
use Customize\Repository\AffiliateStatusRepository;
use Customize\Service\MailService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Eccube\Service\CartService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;

class AnilineEntryController extends AbstractController
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
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var UserPasswordHasherInterface
     */
    protected $passwordHasher;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Eccube\Service\CartService
     */
    protected $cartService;

    /**
     * @var AffiliateStatusRepository
     */
    protected $affiliateStatusRepository;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    public $auth_magic;

    /**
     * @var string
     */
    public $auth_type;

    /**
     * @var string
     */
    public $password_hash_algos;

    /**
     * EntryController constructor.
     *
     * @param CartService $cartService
     * @param CustomerStatusRepository $customerStatusRepository
     * @param MailService $mailService
     * @param BaseInfoRepository $baseInfoRepository
     * @param CustomerRepository $customerRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param ValidatorInterface $validatorInterface
     * @param TokenStorageInterface $tokenStorage
     * @param AffiliateStatusRepository $affiliateStatusRepository
     * @param BreedersRepository $breedersRepository
     * @param ConservationsRepository $conservationsRepository
     * @param FormFactoryInterface $formFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartService $cartService,
        CustomerStatusRepository $customerStatusRepository,
        MailService $mailService,
        BaseInfoRepository $baseInfoRepository,
        CustomerRepository $customerRepository,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validatorInterface,
        TokenStorageInterface $tokenStorage,
        AffiliateStatusRepository $affiliateStatusRepository,
        BreedersRepository $breedersRepository,
        ConservationsRepository $conservationsRepository,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    ) {
        $this->customerStatusRepository = $customerStatusRepository;
        $this->mailService = $mailService;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerRepository = $customerRepository;
        $this->passwordHasher = $passwordHasher;
        $this->recursiveValidator = $validatorInterface;
        $this->tokenStorage = $tokenStorage;
        $this->cartService = $cartService;
        $this->affiliateStatusRepository = $affiliateStatusRepository;
        $this->breedersRepository = $breedersRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->auth_magic = $eccubeConfig->get('eccube_auth_magic');
        $this->auth_type = $eccubeConfig->get('eccube_auth_type');
        $this->password_hash_algos = $eccubeConfig->get('eccube_password_hash_algos');
    }

    /**
     * 利用規約.
     *
     * @Route("/help/agreement", name="help_agreement")
     * @Template("Help/agreement.twig")
     */
    public function agreement(Request $request)
    {
        $referer = $request->headers->get('referer');

        $returnPath = $request->get("ReturnPath");

        if($returnPath == "breeder_mypage"){
            $prefix = "breeder";
        }
        else if($returnPath == "adoption_mypage"){
            $prefix = "adoption";
        }
        else{
            $prefix = "default";
        }

        return ['prefix' => $prefix,];
    }

    /**
     * 会員登録画面.
     *
     * @Route("/entry", name="aniline_entry")
     * @Template("Entry/index.twig")
     */
    public function index(Request $request)
    {
        $returnPath = $request->get("ReturnPath");

        if($returnPath == ""){
            $returnPath = "homepage";
        }

        if($returnPath == "breeder_mypage"){
            $prefix = "breeder";
            $regist_type = 1;
        }
        else if($returnPath == "adoption_mypage"){
            $prefix = "adoption";
            $regist_type = 2;
        }
        else{
            $prefix = "default";
            $regist_type = 0;
        }

        if ($this->isGranted('ROLE_USER')) {
            $this->logger->info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute($returnPath);
        }

        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $this->customerRepository->newCustomer();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createBuilder(EntryType::class, $Customer);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_ENTRY_INDEX_INITIALIZE);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $this->logger->info('会員登録確認開始');
                    $this->logger->info('会員登録確認完了');

                    return $this->render(
                        'Entry/confirm.twig',
                        [
                            'form' => $form->createView(),
                            'request' => $request,
                            'prefix' => $prefix,
                        ]
                    );

                case 'complete':
                    $this->logger->info('会員登録開始');

                    //ブリーダー・保護団体紹介チェック
                    $sessid = $request->cookies->get('rid_key');
                    //$session = $request->getSession();
                    //$sessid = $session->getId();
                    $rid = 0;

                    $this->logger->info("【キャンペーン】チェック開始 sessid=".$sessid);

                    if($sessid != ""){
                        $affiliate = $this->affiliateStatusRepository->findOneBy(array("session_id" => $sessid,"campaign_id" => array(1,2)),array('create_date' => 'DESC'));

                        if($affiliate) {
                            $cid = $affiliate->getCampaignId();
                            $id_hash = $affiliate->getAffiliateKey();
                            if($cid == 1){
                                //ブリーダー
                                $breeder = $this->breedersRepository->findOneBy(array("id_hash" => $id_hash));
                                if($breeder){
                                    $rid = $breeder->getId();
                                }
                                else{
                                    $this->logger->info("【キャンペーン】関連ブリーダーが見つかりません。sessid=".$sessid."  hash=".$id_hash);
                                }
                            } elseif($cid == 2){
                                //保護団体
                                $conservation = $this->conservationsRepository->findOneBy(array("id_hash" => $id_hash));
                                if($conservation){
                                    $rid = $conservation->getId();
                                }
                                else{
                                    $this->logger->info("【キャンペーン】関連保護団体が見つかりません。sessid=".$sessid."  hash=".$id_hash);
                                }
                            }
                        }
                    }

                    // $encoder = $this->passwordHasher->getEncoder($Customer);
                    // $salt = $encoder->createSalt();
                    // $password = $encoder->encodePassword($Customer->getPassword(), $salt);
                    // $secretKey = $this->customerRepository->getUniqueSecretKey();

                    // $Customer
                    //     ->setSalt($salt)
                    //     ->setPassword($password)
                    //     ->setSecretKey($secretKey)
                    //     ->setPoint(0)
                    //     ->setIsBreeder(0)
                    //     ->setRegistType($regist_type)
                    //     ->setIsConservation(0)
                    //     ->setRelationId($rid);

                    // パスワードをハッシュ化（saltは不要）
                    $hashedPassword = $this->passwordHasher->hashPassword($Customer, $Customer->getPlainPassword());

                    $secretKey = $this->customerRepository->getUniqueSecretKey();

                    $Customer
                        ->setPassword($hashedPassword)
                        ->setSecretKey($secretKey)
                        ->setPoint(0)
                        ->setIsBreeder(0)
                        ->setRegistType($regist_type)
                        ->setIsConservation(0)
                        ->setRelationId($rid);

                    $this->entityManager->persist($Customer);
                    $this->entityManager->flush();

                    $this->logger->info('会員登録完了');

                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'Customer' => $Customer,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE);


                    $activateFlg = $this->BaseInfo->isOptionCustomerActivate();

                    // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
                    if ($activateFlg) {
                        if($prefix == "breeder"){
                            $activateUrl = $this->generateUrl('breeder_entry_activate', ['secret_key' => $Customer->getSecretKey(),'returnPath' => $returnPath], UrlGeneratorInterface::ABSOLUTE_URL);
                        }
                        else {
                            $activateUrl = $this->generateUrl('entry_activate', ['secret_key' => $Customer->getSecretKey(),'returnPath' => $returnPath], UrlGeneratorInterface::ABSOLUTE_URL);
                        }
                        

                        // メール送信
                        $this->mailService->sendCustomerConfirmMail($Customer, $activateUrl);

                        if ($event->hasResponse()) {
                            return $event->getResponse();
                        }

                        $this->logger->info('仮会員登録完了画面へリダイレクト');

                        return $this->redirectToRoute('entry_complete',['ReturnPath' => $returnPath,]);

                    } else {
                        // 仮会員設定が無効な場合は、会員登録を完了させる.
                        $qtyInCart = $this->entryActivate($request, $Customer->getSecretKey(),$prefix);

                        // URLを変更するため完了画面にリダイレクト
                        return $this->redirectToRoute('entry_activate', [
                            'secret_key' => $Customer->getSecretKey(),
                            'qtyInCart' => $qtyInCart,
                            'returnPath' => $returnPath,
                            'prefix' => $prefix,
                        ]);

                    }
            }
        }

        //問い合わせから来た場明の判定とセッション変数取得
        $contact_save = $request->cookies->get('contact_save');

        return [
            'returnPath' => $returnPath,
            'form' => $form->createView(),
            'request' => $request,
            'prefix' => $prefix,
            'contact_save' => $contact_save
        ];
        // return $this->render('Entry/index.twig', [
        //     'returnPath' => $returnPath,
        //     'form' => $form->createView(),
        //     'request' => $request,
        //     'prefix' => $prefix,
        //     'contact_save' => $contact_save
        // ]);
    }

    /**
     * 会員登録完了画面.
     *
     * @Route("/entry/complete", name="entry_complete")
     * @Template("Entry/complete.twig")
     */
    public function complete(Request $request)
    {
        $returnPath = $request->get("ReturnPath");

        if($returnPath == "breeder_mypage"){
            $prefix = "breeder";
        }
        else if($returnPath == "adoption_mypage"){
            $prefix = "adoption";
        }
        else{
            $prefix = "default";
        }

        return ['prefix' => $prefix,'request' => $request];
    }

    /**
     * 会員登録完了画面.
     *
     * @Route("/entry/completed", name="entry_completed")
     * @Template("Entry/completed.twig")
     */
    public function completed(Request $request)
    {
        return ['request' => $request];
    }

    /**
     * 会員のアクティベート（本会員化）を行う.
     *
     * @Route("/entry/activate/{secret_key}/{returnPath}/{qtyInCart}", name="entry_activate")
     * @Route("/entry/activate/{secret_key}/", name="entry_activate_noret")
     * @Template("Entry/activate.twig")
     */
    public function activate(Request $request, $secret_key, $returnPath = null, $qtyInCart = null)
    {
        if(!$returnPath){$returnPath = "homepage";}

        if($returnPath == "breeder_mypage"){
            $prefix = "breeder";
        }
        else if($returnPath == "adoption_mypage"){
            $prefix = "adoption";
        }
        else{
            $prefix = "default";
        }

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

        if(!is_null($qtyInCart)) {

            return [
                'qtyInCart' => $qtyInCart,
                'returnPath' => $returnPath,
                'prefix' => $prefix,
            ];
        } elseif ($request->getMethod() === 'GET' && count($errors) === 0) {

            // 会員登録処理を行う
            $qtyInCart = $this->entryActivate($request, $secret_key,$prefix);
            if($qtyInCart == -1){
                return $this->redirectToRoute("entry_completed");
            }
            return [
                'qtyInCart' => $qtyInCart,
                'returnPath' => $returnPath,
                'prefix' => $prefix,
            ];
        }

        throw new HttpException\NotFoundHttpException();
    }

    /**
     * 会員のアクティベート（本会員化）を行う.
     *
     * @Route("/breeder/entry/activate/{secret_key}/{returnPath}/{qtyInCart}", name="breeder_entry_activate")
     * @Route("/breeder/entry/activate/{secret_key}/", name="breeder_entry_activate_noret")
     * @Template("Entry/activate.twig")
     */
    public function breeder_activate(Request $request, $secret_key, $returnPath = null, $qtyInCart = null)
    {
        if(!$returnPath){$returnPath = "homepage";}

        if($returnPath == "breeder_mypage"){
            $prefix = "breeder";
        }
        else if($returnPath == "adoption_mypage"){
            $prefix = "adoption";
        }
        else{
            $prefix = "default";
        }

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

        if(!is_null($qtyInCart)) {

            return [
                'qtyInCart' => $qtyInCart,
                'returnPath' => $returnPath,
                'prefix' => $prefix,
            ];
        } elseif ($request->getMethod() === 'GET' && count($errors) === 0) {

            // 会員登録処理を行う
            $qtyInCart = $this->entryActivate($request, $secret_key,$prefix);

            //問い合わせからの会員登録の場合はマイページに遷移し問い合わせ実行
            $contact_save = $request->cookies->get('contact_save');
            if($contact_save){
                return $this->redirectToRoute("breeder_mypage");
            }
            //ここまで

            if($qtyInCart == -1){
                return $this->redirectToRoute("entry_completed");
            }
            return [
                'qtyInCart' => $qtyInCart,
                'returnPath' => $returnPath,
                'prefix' => $prefix,
            ];
        }

        throw new HttpException\NotFoundHttpException();
    }


    /**
     * 会員登録処理を行う
     *
     * @param Request $request
     * @param $secret_key
     * @return \Eccube\Entity\Cart|mixed
     */
    private function entryActivate(Request $request, $secret_key,$prefix)
    {
        $this->logger->info('本会員登録開始');
        $Customer = $this->customerRepository->getProvisionalCustomerBySecretKey($secret_key);
        if (is_null($Customer)) {
            return -1;
            //throw new HttpException\NotFoundHttpException();
        }

        $CustomerStatus = $this->customerStatusRepository->find(CustomerStatus::REGULAR);
        $Customer->setStatus($CustomerStatus);
        $this->entityManager->persist($Customer);
        $this->entityManager->flush();

        $this->logger->info('本会員登録完了');

        $event = new EventArgs(
            [
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_ENTRY_ACTIVATE_COMPLETE);

        // メール送信
        $this->mailService->sendCustomerCompleteMail($Customer,$prefix);

        // Assign session carts into customer carts
        $Carts = $this->cartService->getCarts();
        $qtyInCart = 0;
        foreach ($Carts as $Cart) {
            $qtyInCart += $Cart->getTotalQuantity();
        }

        // 本会員登録してログイン状態にする
        $token = new UsernamePasswordToken($Customer, 'breeder', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
        $request->getSession()->migrate(true);

        if ($qtyInCart) {
            $this->cartService->save();
        }

        $this->logger->info('ログイン済に変更', [$this->getUser()->getId()]);

        return $qtyInCart;

    }

}
