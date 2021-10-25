<?php

namespace Customize\Controller\Breeder;

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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;
use Customize\Config\AnilineConf;

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
        $builder = $this->formFactory->createBuilder(BreedersType::class, $breederData);

        $form = $builder->getForm();
        $form->handleRequest($request);

        // フォームサブミット時に、画像サムネイル情報を設定する(バリデーションは無視)
        if ($form->isSubmitted()){
            $thumbnail_path = $request->get('thumbnail_path') ?: $breederData->getThumbnailPath();
            $license_thumbnail_path = $request->get('license_thumbnail_path') ?: $breederData->getLicenseThumbnailPath();

            $breederData->setThumbnailPath($thumbnail_path);
            $breederData->setLicenseThumbnailPath($license_thumbnail_path);
            $this->entityManager->persist($breederData);
            $this->entityManager->flush();

            // どちらかが空欄の場合、強制的に基本情報入力ページにリターン
            if ($thumbnail_path == "" || $license_thumbnail_path == "") {
                return $this->redirectToRoute('breeder_baseinfo');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // $thumbnail_path = $request->get('thumbnail_path') ?: $breederData->getThumbnailPath();
            // $license_thumbnail_path = $request->get('license_thumbnail_path') ?: $breederData->getLicenseThumbnailPath();

            $handling_pet_kind = $form->getData()->getHandlingPetKind();

            if ($handling_pet_kind == AnilineConf::ANILINE_PET_KIND_DOG) {
                $breederData->setBreederHouseNameCat(null);
            } elseif ($handling_pet_kind == AnilineConf::ANILINE_PET_KIND_CAT) {
                $breederData->setBreederHouseNameDog(null);
            }

            // if (!$thumbnail_path || !$license_thumbnail_path) {
            //     if ($thumbnail_path) {
            //         $breederData->setThumbnailPath($thumbnail_path);
            //     } elseif ($license_thumbnail_path) {
            //         $breederData->setLicenseThumbnailPath($license_thumbnail_path);
            //     }
                
            //     return $this->redirectToRoute('breeder_baseinfo');
            // }

            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense());
                // ->setThumbnailPath($thumbnail_path)
                // ->setLicenseThumbnailPath($license_thumbnail_path);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $entityManager->flush();
            return $this->redirectToRoute($return_path);
        }

        return [
            'return_path' => $return_path,
            'breederData' => $breederData,
            'form' => $form->createView(),
            'Customer' => $user,
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
        return [];
    }
}
