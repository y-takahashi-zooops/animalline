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


use Customize\Config\AnilineConf;
use Customize\Form\Type\Admin\ConservationHouseType;
use Customize\Repository\BreedsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Entity\Conservations;
use Customize\Entity\ConservationPets;
use Customize\Form\Type\Admin\ConservationPetsType;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Form\Type\Admin\ConservationsType;
use Customize\Service\AdoptionQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetsRepository;
     */
    protected $conservationPetsRepository;

    /**
     * @var BreedsRepository;
     */
    protected $breedsRepository;

    /**
     * @var AdoptionQueryService;
     */
    protected $adoptionQueryService;

    /**
     * @var CoatColorsRepository
     */
    protected $coatColorsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param AdoptionQueryService $adoptionQueryService
     */

    public function __construct(
        ConservationsRepository        $conservationsRepository,
        BreedsRepository               $breedsRepository,
        CoatColorsRepository           $coatColorsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        AdoptionQueryService           $adoptionQueryService
    )
    {
        $this->conservationsRepository = $conservationsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->coatColorsRepository = $coatColorsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/adoption_list", name="admin_adoption_list")
     * @Template("@admin/Adoption/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();
        $results = $this->conservationsRepository->searchConservations($request);
        $conservations = $paginator->paginate(
            $results,
            $request['page'] ?? 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('@admin/Adoption/index.twig', [
            'conservations' => $conservations,
            'direction' => !isset($request['direction']) || $request['direction'] === 'DESC' ? 'ASC' : 'DESC',
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/edit/{id}", name="admin_adoption_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/edit.twig")
     */
    public function Edit(Request $request, Conservations $conservations)
    {
        $form = $this->createForm(ConservationsType::class, $conservations);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservations);
            $entityManager->flush();
            return $this->redirectToRoute('admin_adoption_list');
        }
        return $this->render('@admin/Adoption/edit.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/house/{id}", name="admin_adoption_house", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/house.twig")
     */
    public function House(Request $request, Conservations $conservations)
    {
        $conservationsHouse = null;
        $conservationsHouses = $conservations->getConservationsHouses();
        if (!$conservationsHouses->isEmpty()) {
            $conservationsHouse = $conservationsHouses->first();
        }
        if ($request->get('pet_type')) {
            $conservationsHouse = $conservations->getConservationHouseByPetType($request->query->getInt('pet_type'));
        }
        if (!$conservationsHouse || !$conservationsHouse->getId()) {
            throw new HttpException\NotFoundHttpException();
        }

        $form = $this->createForm(ConservationHouseType::class, $conservationsHouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conservationsHouse->setConservationHousePref($conservationsHouse->getPref());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationsHouse);
            $entityManager->flush();
            return $this->redirectToRoute('admin_adoption_list');
        }
        return $this->render('@admin/Adoption/house.twig', [
            'conservations' => $conservations,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/examination/{id}", name="admin_adoption_examination", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/examination.twig")
     */
    public function Examination(Request $request)
    {
        return;
    }

    /**
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

        $breeds = $this->breedsRepository->findAll();

        return $this->render('@admin/Adoption/pet/index.twig', [
            'conservationId' => $request->get('id'),
            'breeds' => $breeds,
            'pets' => $pets,
            'direction' => $request->get('direction') == 'ASC' ? 'DESC' : 'ASC'
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/pet/edit/{id}", name="admin_adoption_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/pet/edit.twig")
     */
    public function pet_edit(Request $request, ConservationPets $conservationPet)
    {
        $builder = $this->formFactory->createBuilder(ConservationPetsType::class, $conservationPet);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coatColor = $this->coatColorsRepository->find($request->get('coat_color'));
            $breedType = $this->breedsRepository->find($request->get('breeds_type'));
            $conservationPet->setBreedsType($breedType)
                ->setCoatColor($coatColor);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPet);
            $entityManager->flush();

            return $this->redirectToRoute('admin_adoption_pet_list', ['id' => $conservationPet->getConservation()->getId()]);
        }

        $breeds = $this->breedsRepository->findBy(['pet_kind' => $conservationPet->getPetKind()]);
        $colors = $this->coatColorsRepository->findBy(['pet_kind' => $conservationPet->getPetKind()]);
        $images = $this->conservationPetImageRepository->findBy(['ConservationPet' => $conservationPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE]);
        return $this->render('@admin/Adoption/pet/edit.twig', [
            'conservationPet' => $conservationPet,
            'breeds' => $breeds,
            'colors' => $colors,
            'images' => $images,
            'form' => $form->createView(),
        ]);
    }
}
