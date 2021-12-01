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

namespace Customize\Controller\Admin\Breeder;

use Customize\Form\Type\Admin\BreedersType;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Service\BreederQueryService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetImageRepository;
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
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

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
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     */
    public function __construct(
        BreedersRepository               $breedersRepository,
        BreedsRepository                 $breedsRepository,
        BreederPetImageRepository        $breederPetImageRepository,
        BreederQueryService              $breederQueryService,
        BreederPetsRepository            $breederPetsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        CustomerRepository               $customerRepository,
        MailService                      $mailService
    ) {
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->breederPetImageRepository = $breederPetImageRepository;
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

        if (array_key_exists('create_date_from', $request)) {
            $criteria['create_date_from'] = $request['create_date_from'];
        }
        if (array_key_exists('create_date_to', $request)) {
            $criteria['create_date_to'] = $request['create_date_to'];
        }

        if (array_key_exists('update_date_from', $request)) {
            $criteria['update_date_from'] = $request['update_date_from'];
        }
        if (array_key_exists('update_date_to', $request)) {
            $criteria['update_date_to'] = $request['update_date_to'];
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

        $breederstatus[0] = "申請待ち";
        $breederstatus[1] = "未審査";
        $breederstatus[2] = "審査済（許可）";
        $breederstatus[3] = "審査済（拒否）";

        return $this->render('@admin/Breeder/index.twig', [
            'breeders' => $breeders,
            'direction' => $direction,
            'breederstatus' => $breederstatus,
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

        $builder = $this->formFactory->createBuilder(BreedersType::class, $breederData);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $thumbnailPath = $request->get('thumbnail_path') ?: $breederData->getThumbnailPath();
        $licenseThumbnailPath = $request->get('license_thumbnail_path') ?: $breederData->getLicenseThumbnailPath();

        if ($form->isSubmitted() && $form->isValid()) {
            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense())
                ->setThumbnailPath($thumbnailPath)
                ->setLicenseThumbnailPath($licenseThumbnailPath);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $entityManager->flush();
            return $this->redirectToRoute('admin_breeder_list');
        }

        return [
            'thumbnailPath' => $thumbnailPath,
            'licenseThumbnailPath' => $licenseThumbnailPath,
            'breederData' => $breederData,
            'form' => $form->createView(),
            'id' => $request->get('id')
        ];
    }

    /**
     * 登録内容編集ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/breeder/dna_check/list", name="admin_breeder_dna_check_list")
     * @Template("@admin/Breeder/dna_check_list.twig")
     */
    public function dnaCheckList(Request $request)
    {
        return[];
    }
}
