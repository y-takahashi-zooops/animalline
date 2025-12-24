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

namespace Customize\Controller\Admin\Adoption;

use Customize\Config\AnilineConf;
use Customize\Repository\BreedsRepository;
use Customize\Entity\ConservationPets;
use Customize\Form\Type\Admin\ConservationPetsType;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\AdoptionQueryService;
use DateTime;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionPetController extends AbstractController
{
    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * AdoptionPetController constructor.
     *
     * @param BreedsRepository $breedsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param AdoptionQueryService $adoptionQueryService
     */

    public function __construct(
        BreedsRepository               $breedsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        AdoptionQueryService           $adoptionQueryService
    ) {
        $this->breedsRepository = $breedsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->adoptionQueryService = $adoptionQueryService;
    }

    /**
     * ペット一覧保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/pet/list/{id}", name="admin_adoption_pet_list", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/pet/index.twig")
     */
    public function pet_index(PaginatorInterface $paginator, Request $request)
    {
        $criteria['conservation_id'] = $request->get('id');

        switch ($request->get('pet_kind')) {
            case 1:
                $criteria['pet_kind'] = AnilineConf::ANILINE_PET_KIND_DOG;
                break;
            case 2:
                $criteria['pet_kind'] = AnilineConf::ANILINE_PET_KIND_CAT;
                break;
            default:
                break;
        }

        if ($request->get('breed_type')) {
            $criteria['breed_type'] = $request->get('breed_type');
        }

        $field = $request->get('field') ?? 'create_date';
        $direction = $request->get('direction') ?? 'DESC';
        $order['field'] = $field;
        $order['direction'] = $direction;

        $results = $this->adoptionQueryService->filterPetAdmin($criteria, $order);
        $pets = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        // $breeds = $this->breedsRepository->findBy([], ['breeds_name' => 'ASC']);
        $breeds = $this->breedsRepository->findBy([], ['sort_order' => 'ASC']);

        return $this->render('@admin/Adoption/pet/index.twig', [
            'conservationId' => $request->get('id'),
            'breeds' => $breeds,
            'pets' => $pets,
            'direction' => $request->get('direction') == 'ASC' ? 'DESC' : 'ASC'
        ]);
    }

    /**
     * ペット情報編集保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/pet/edit/{id}", name="admin_adoption_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/pet/edit.twig")
     */
    public function pet_edit(Request $request, ConservationPets $conservationPet)
    {
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conservationPet->setBreedsType($this->breedsRepository->find($request->get('breeds_type')));

            $entityManager = $this->entityManager;
            $entityManager->persist($conservationPet);
            $entityManager->flush();

            return $this->redirect($request->get('url'));
        }

        // $breeds = $this->breedsRepository->findBy(['pet_kind' => $conservationPet->getPetKind()], ['breeds_name' => 'ASC']);
        $breeds = $this->breedsRepository->findBy(['pet_kind' => $conservationPet->getPetKind()], ['sort_order' => 'ASC']);
        $images = $this->conservationPetImageRepository->findBy(['ConservationPet' => $conservationPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE]);

        return $this->render('@admin/Adoption/pet/edit.twig', [
            'form' => $form->createView(),
            'conservationPet' => $conservationPet,
            'breeds' => $breeds,
            'images' => $images,
        ]);
    }

    /**
     * ペット情報管理
     *
     * @Route("/%eccube_admin_route%/adoption/pet/{id}/change_status", name="admin_adoption_pet_change_status")
     * @param ConservationPets $pet
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function pet_change_status(ConservationPets $pet)
    {
        $newStatus = !$pet->getIsActive();
        $pet->setIsActive($newStatus);
        if ($newStatus) $pet->setReleaseDate(new DateTime);
        $em = $this->entityManager;
        $em->persist($pet);
        $em->flush();

        return new JsonResponse([
        ]);
    }

    /**
     * Download PDF
     *
     * @Route("/%eccube_admin_route%/adoption/pet/{id}/dna/download_pdf", requirements={"id" = "\d+"}, name="admin_adoption_pet_dna_download_pdf")
     *
     * @return BinaryFileResponse
     */
    public function downloadPdf(ConservationPets $pet, DnaCheckStatusRepository $dnaCheckStatusRepository): BinaryFileResponse
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

}
