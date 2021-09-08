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

namespace Customize\Controller\Admin;

use Customize\Entity\Breeders;
use Customize\Form\Type\Admin\BreederExaminationInfoType;
use Customize\Form\Type\AdminBreederType;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Service\BreederQueryService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederPets;
use Customize\Entity\BreederExaminationInfo;
use Customize\Form\Type\Admin\BreederHouseType;
use Customize\Form\Type\Admin\BreederPetsType;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\CoatColorsRepository;
use Eccube\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;

class BreederController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var CoatColorsRepository
     */
    protected $coatColorsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederHouseRepository
     */
    protected $breederHouseRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * breederController constructor.
     * @param BreedersRepository $breedersRepository
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        BreedersRepository               $breedersRepository,
        BreedsRepository                 $breedsRepository,
        CoatColorsRepository             $coatColorsRepository,
        BreederPetImageRepository        $breederPetImageRepository,
        BreederQueryService              $breederQueryService,
        BreederPetsRepository            $breederPetsRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        CustomerRepository               $customerRepository
    ) {
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->coatColorsRepository = $coatColorsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/breeder_list", name="admin_breeder_list")
     * @Template("@admin/Breeder/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();
        $criteria = [];
        if (array_key_exists('breeder_name', $request)) {
            $criteria['breeder_name'] = $request['breeder_name'];
        }

        if (array_key_exists('examination_status', $request)) {
            switch ($request['examination_status']) {
                case 2:
                    $criteria['examination_status'] = [AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK, AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG];
                    break;
                case 3:
                    $criteria['examination_status'] = [AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK];
                    break;
                default:
                    break;
            }
        }

        $order = [];
        $order['field'] = array_key_exists('field', $request) ? $request['field'] : 'create_date';
        $order['direction'] = array_key_exists('direction', $request) ? $request['direction'] : 'DESC';

        $results = $this->breedersRepository->filterBreederAdmin($criteria, $order);

        $breeders = $paginator->paginate(
            $results,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $direction = 'ASC';
        if (array_key_exists('direction', $request)) {
            $direction = $request['direction'] == 'ASC' ? 'DESC' : 'ASC';
        }

        return $this->render('@admin/Breeder/index.twig', [
            'breeders' => $breeders,
            'direction' => $direction
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/edit/{id}", name="admin_breeder_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/edit.twig")
     */
    public function Edit(Request $request, BreedersRepository $breedersRepository)
    {
        $breederData = $breedersRepository->find($request->get('id'));

        $builder = $this->formFactory->createBuilder(AdminBreederType::class, $breederData);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $thumbnail_path = $request->get('thumbnail_path') ? $request->get('thumbnail_path') : $breederData->getThumbnailPath();

        if ($form->isSubmitted() && $form->isValid()) {
            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense())
                ->setThumbnailPath($thumbnail_path);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $entityManager->flush();
            return $this->redirectToRoute('admin_breeder_list');
        }

        return [
            'breederData' => $breederData,
            'form' => $form->createView(),
            'id' => $request->get('id')
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/house/{id}", name="admin_breeder_house", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/house.twig")
     */
    public function House(Request $request, Breeders $breeder)
    {
        $houses = $this->breederHouseRepository->findBy(['Breeder' => $breeder]);
        if (!$houses) throw new NotFoundHttpException();
        $house = $houses[0]; // show first house by default.
        $isEnablePetType = count($houses) > 1; // only allow select pet type if breeder have both.

        $petType = $request->get('pet_type'); // from GET request to show house by pet type.
        if ($petType) {
            $house = $this->breederHouseRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => $petType]);
            if (!$house) throw new NotFoundHttpException();
        }

        $form = $this->createForm(BreederHouseType::class, $house);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $house->setBreederHousePref($house->getBreederHousePrefId()['name'] ?? '');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($house);
            $entityManager->flush();

            return $this->redirectToRoute('admin_breeder_list');
        }

        return [
            'form' => $form->createView(),
            'house' => $house,
            'isEnablePetType' => $isEnablePetType
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/examination/{id}", name="admin_breeder_examination", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/examination.twig")
     */
    public function Examination(Request $request)
    {
        $breeder = $this->breedersRepository->find($request->get('id'));
        $breederExaminationInfos = $this->breederExaminationInfoRepository->findBy(['Breeder' => $breeder]);
        if (!$breederExaminationInfos) throw new NotFoundHttpException();
        $breederExaminationInfo = $breederExaminationInfos[0];
        $isEnablePetType = count($breederExaminationInfos) > 1;
        if ($request->get('pet_type')) {
            $breederExaminationInfo = $this->breederExaminationInfoRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => $request->get('pet_type')]);
            if (!$breederExaminationInfo) throw new NotFoundHttpException();
        }

        $form = $this->createForm(BreederExaminationInfoType::class, $breederExaminationInfo, ['disabled' => true]);
        $form->handleRequest($request);
        return [
            'form' => $form->createView(),
            'petType' => $breederExaminationInfo->getPetType() == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫',
            'isEnablePetType' => $isEnablePetType,
            'breederExaminationInfo' => $breederExaminationInfo
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/examination/regist/{id}", name="admin_breeder_examination_regist", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/examination_regist.twig")
     */
    public function Examination_regist(Request $request, BreederExaminationInfo $examination)
    {
        $breederId = $examination->getBreeder()->getId();
        $customer = $this->customerRepository->find($breederId);
        if (!$customer) throw new NotFoundHttpException();

        if ($request->isMethod('POST')) {
            $result = (int)$request->get('examination_result');
            $examination->setExaminationResult($result)
                ->setExaminationResultComment($request->get('examination_result_comment'));
            if ($result === AnilineConf::ANILINE_EXAMINATION_RESULT_DECISION_OK) $customer->setIsBreeder(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($examination);
            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('admin_breeder_examination', ['id' => $breederId]);
        }

        $data = [
            'name' => "{$customer->getName01()} {$customer->getName02()}",
            'examination_comment' => $examination->getExaminationResultComment()
        ];

        return compact(
            'examination',
            'data'
        );
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/pet/list/{id}", name="admin_breeder_pet_list", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/index.twig")
     */
    public function pet_index(PaginatorInterface $paginator, Request $request)
    {
        $criteria = [];
        $criteria['id'] = $request->get('id');
        $breeds = $this->breedsRepository->findAll();

        switch ($request->get('pet_kind')) {
            case 1:
                $criteria['pet_kind'] = [AnilineConf::ANILINE_PET_KIND_DOG];
                break;
            case 2:
                $criteria['pet_kind'] = [AnilineConf::ANILINE_PET_KIND_CAT];
                break;
            default:
                break;
        }


        if ($request->get('breed_type')) {
            $criteria['breed_type'] = $request->get('breed_type');
        }

        $order = [];
        $field = $request->get('field') ?? 'create_date';
        $direction = $request->get('direction') ?? 'DESC';
        $order['field'] = $field;
        $order['direction'] = $direction;

        $results = $this->breederQueryService->filterPetAdmin($criteria, $order);
        $pets = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $direction = 'ASC';
        if ($request->get('direction')) {
            $direction = $request->get('direction') == 'ASC' ? 'DESC' : 'ASC';
        }

        return $this->render('@admin/Breeder/pet/index.twig', [
            'id' => $request->get('id'),
            'pets' => $pets,
            'direction' => $direction,
            'breeds' => $breeds
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/pet/edit/{id}", name="admin_breeder_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/edit.twig")
     */
    public function pet_edit(Request $request, BreederPets $breederPet)
    {
        $form = $this->createForm(BreederPetsType::class, $breederPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $breederPet->setBreedsType($this->breedsRepository->find($request->get('breeds_type')));
            $breederPet->setCoatColor($this->coatColorsRepository->find($request->get('coat_color')));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederPet);
            $entityManager->flush();

            return $this->redirectToRoute('admin_breeder_pet_list', ['id' => $breederPet->getBreeder()->getId()]);
        }

        $breeds = $this->breedsRepository->findBy(['pet_kind' => $breederPet->getPetKind()]);
        $colors = $this->coatColorsRepository->findBy(['pet_kind' => $breederPet->getPetKind()]);
        $images = $this->breederPetImageRepository->findBy(['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE]);

        return [
            'form' => $form->createView(),
            'breederPet' => $breederPet,
            'breeds' => $breeds,
            'colors' => $colors,
            'images' => $images
        ];
    }
}
