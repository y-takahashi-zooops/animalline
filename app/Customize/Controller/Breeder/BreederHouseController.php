<?php

namespace Customize\Controller\Breeder;

use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use Carbon\Carbon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\BreedersType;
use Customize\Form\Type\Breeder\BreederHouseType;
use Customize\Entity\Breeders;
use Customize\Entity\BreederHouse;
use Customize\Repository\BreederPetsRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\Breeder\BreederContactType;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BreederHouseController extends AbstractController
{
    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederHouse
     */
    protected $breederHouseRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * BreederController constructor.
     *
     * @param BreederQueryService $breederQueryService
     * @param BreedersRepository $breedersRepository
     * @param PrefRepository $prefRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        BreederQueryService              $breederQueryService,
        BreedersRepository               $breedersRepository,
        PrefRepository                   $prefRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederPetsRepository            $breederPetsRepository,
        CustomerRepository               $customerRepository
    ) {
        $this->breederQueryService = $breederQueryService;
        $this->breedersRepository = $breedersRepository;
        $this->prefRepository = $prefRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->customerRepository = $customerRepository;
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

        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnail_path = $request->get('thumbnail_path') ? $request->get('thumbnail_path') : $breederData->getThumbnailPath();
            $license_thumbnail_path = $request->get('license_thumbnail_path') ? $request->get('license_thumbnail_path') : $breederData->getLicenseThumbnailPath();

            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense())
                ->setThumbnailPath($thumbnail_path)
                ->setLicenseThumbnailPath($license_thumbnail_path);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $entityManager->flush();
            return $this->redirectToRoute($return_path);
        } elseif (!$form->isSubmitted()) {

            // Customer情報から初期情報をセット
            $Customer = $this->customerRepository->find($user);
            $form->get('breeder_name')->setData($Customer->getname01() . '　' . $Customer->getname02());
            $form->get('breeder_kana')->setData($Customer->getkana01() . '　' . $Customer->getkana02());
            $form->get('breeder_zip')->setData($Customer->getPostalCode());
            $form->get('addr')->get('PrefBreeder')->setData($Customer->getPref());
            $form->get('addr')->get('breeder_city')->setData($Customer->getAddr01());
            $form->get('addr')->get('breeder_address')->setData($Customer->getAddr02());
            $form->get('breeder_tel')->setData($Customer->getPhoneNumber());
        }

        return [
            'return_path' => $return_path,
            'breederData' => $breederData,
            'form' => $form->createView()
        ];
    }

    /**
     * 犬舎・猫舎情報編集画面
     *
     * @Route("/breeder/member/house_info/{pet_type}", name="breeder_house_info")
     * @Template("/animalline/breeder/member/house_info.twig")
     */
    public function house_info(Request $request)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if ($return_path == "") {
            $return_path = "breeder_examination";
        }

        $petType = $request->get('pet_type');
        $breeder = $this->breedersRepository->find($this->getUser());
        $breederHouse = $this->breederHouseRepository->findOneBy(['pet_type' => $petType, 'Breeder' => $breeder]);
        if (!$breederHouse) {
            $breederHouse = new BreederHouse();
        }
        $builder = $this->formFactory->createBuilder(BreederHouseType::class, $breederHouse);
        $breeder = $this->breedersRepository->find($this->getUser());

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $housePref = $breederHouse->getBreederHousePrefId();
            $breederHouse->setBreeder($breeder)
                ->setPetType($petType)
                ->setBreederHousePref($housePref['name']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederHouse);

            $entityManager->flush();

            return $this->redirectToRoute($return_path);
        }
        return [
            'return_path' => $return_path,
            'form' => $form->createView(),
            'petType' => $petType,
            'breeder' => $breeder,
        ];
    }
}
