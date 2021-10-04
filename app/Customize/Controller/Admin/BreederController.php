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
use Customize\Form\Type\Admin\BreederHouseType;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\CoatColorsRepository;
use Eccube\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Service\MailService;

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
     * @var MailService
     */
    protected $mailService;

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
     * @param MailService $mailService
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
        CustomerRepository               $customerRepository,
        MailService                      $mailService
    )
    {
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->coatColorsRepository = $coatColorsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
    }

    /**
     * ブリーダー一覧
     *
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
     * 登録内容編集ブリーダー管理
     *
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
     * 犬舎・猫舎情報編集ブリーダー管理
     *
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
}
