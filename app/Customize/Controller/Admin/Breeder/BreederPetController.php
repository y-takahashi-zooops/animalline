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

use Customize\Repository\BreedsRepository;
use Customize\Service\BreederQueryService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederPets;
use Customize\Form\Type\Admin\BreederPetsType;
use Customize\Repository\BreederPetImageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

class BreederPetController extends AbstractController
{
    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * BreederPetController constructor.
     * @param BreedsRepository $breedsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param BreederPetsRepository $breederPetsRepository
     */
    public function __construct(
        BreedsRepository          $breedsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederQueryService       $breederQueryService,
        BreederPetsRepository       $breederPetsRepository
    ) {
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederPetsRepository = $breederPetsRepository;
    }

    // 廃止予定
    /**
     * ペット情報管理
     *
     * @Route("/%eccube_admin_route%/breeder/pet", name="admin_breeder_pet_all")

     * @Template("@admin/Breeder/pet/all.twig")
     */
    public function pet_all(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();

        // $breeds = $this->breedsRepository->findBy([], ['breeds_name' => 'ASC']);
        $breeds = $this->breedsRepository->findBy([], ['sort_order' => 'ASC']);
        $order = [];
        $order['field'] = array_key_exists('field', $request) ? $request['field'] : 'create_date';
        $order['direction'] = array_key_exists('direction', $request) ? $request['direction'] : 'DESC';
        $criteria = [];
        $criteria['pet_kind'] = array_key_exists('pet_kind', $request) ? $request['pet_kind'] : '';
        $criteria['breed_type'] = array_key_exists('breed_type', $request) ? $request['breed_type'] : '';
        $criteria['public_status'] = array_key_exists('public_status', $request) ? $request['public_status'] : '';
        $criteria['create_date'] = array_key_exists('create_date', $request) ? $request['create_date'] : '';
        $criteria['update_date'] = array_key_exists('update_date', $request) ? $request['update_date'] : '';

        $results = $this->breederPetsRepository->filterBreederPetsAdmin($criteria, $order);

        $breederPets = $paginator->paginate(
            $results,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        $direction = 'ASC';
        if (array_key_exists('direction', $request)) {
            $direction = $request['direction'] == 'ASC' ? 'DESC' : 'ASC';
        }

        return [
            'breeds' => $breeds,
            'direction' => $direction,
            'breederPets' => $breederPets
        ];
    }

    /**
     * ペット情報管理
     *
     * @Route("/%eccube_admin_route%/breeder/pet/{id}/change_status", name="admin_breeder_pet_change_status")
     * @param BreederPets $pet
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function pet_change_status(BreederPets $pet)
    {
        $newStatus = !$pet->getIsActive();
        $pet->setIsActive($newStatus);
        if ($newStatus) $pet->setReleaseDate(new DateTime);
        $em = $this->entityManager;
        $em->persist($pet);
        $em->flush();

        return $this->redirectToRoute('admin_breeder_pet_all');
    }

    /**
     * Download PDF
     *
     * @Route("/%eccube_admin_route%/breeder/pet/{id}/dna/download_pdf", requirements={"id" = "\d+"}, name="admin_breeder_pet_dna_download_pdf")
     *
     * @return BinaryFileResponse
     */
    public function downloadPdf(BreederPets $pet, DnaCheckStatusRepository $dnaCheckStatusRepository): BinaryFileResponse
    {
        $dnaCheckStatus = $dnaCheckStatusRepository->findOneBy(['pet_id' => $pet->getId()]);
        if (!$dnaCheckStatus || !$pdfPath = $dnaCheckStatus->getFilePath()) {
            throw new NotFoundHttpException('PDF DNA not found!');
        }
        $nameArr = explode('/', $pdfPath);
        $fileName = end($nameArr);
        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;
    }

    /**
     * ブリーダーペット一覧
     *
     * @Route("/%eccube_admin_route%/breeder/pet/list/{id}", name="admin_breeder_pet_list", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/index.twig")
     */
    public function pet_index(PaginatorInterface $paginator, Request $request)
    {
        $criteria = [];
        $criteria['id'] = $request->get('id');
        // $breeds = $this->breedsRepository->findBy([], ['breeds_name' => 'ASC']);
        $breeds = $this->breedsRepository->findBy([], ['sort_order' => 'ASC']);

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
     * ペット情報編集
     *
     * @Route("/%eccube_admin_route%/breeder/pet/edit/{id}", name="admin_breeder_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/edit.twig")
     */
    public function pet_edit(Request $request, BreederPets $breederPet)
    {
        $oldStatus = $breederPet->getIsActive();
        $form = $this->createForm(BreederPetsType::class, $breederPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $breederPet->setBreedsType($this->breedsRepository->find($request->get('breeds_type')));

            $curStatus = $breederPet->getIsActive();
            if ($curStatus !== $oldStatus) {
                $breederPet->setReleaseDate($curStatus === AnilineConf::IS_ACTIVE_PUBLIC ? new DateTime : null);
            }

            $entityManager = $this->entityManager;
            $entityManager->persist($breederPet);
            $entityManager->flush();

            return $this->redirect($request->get('url'));
        }

        // $breeds = $this->breedsRepository->findBy(['pet_kind' => $breederPet->getPetKind()], ['breeds_name' => 'ASC']);
        $breeds = $this->breedsRepository->findBy(['pet_kind' => $breederPet->getPetKind()], ['sort_order' => 'ASC']);
        $images = $this->breederPetImageRepository->findBy(['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE]);

        return [
            'form' => $form->createView(),
            'breederPet' => $breederPet,
            'breeds' => $breeds,
            'images' => $images
        ];
    }
}
