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
use Customize\Entity\Breeders;
use Customize\Entity\ConservationPetImage;
use Customize\Form\Type\Admin\ConservationPetsType;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Entity\Conservations;
use Customize\Form\Type\Admin\ConservationsType;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     */

    public function __construct(
        ConservationsRepository $conservationsRepository
    ) {
        $this->conservationsRepository = $conservationsRepository;
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
    public function House(Request $request)
    {
        return;
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
    public function pet_index(Request $request)
    {
        return;
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/pet/edit/{id}", name="admin_adoption_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/pet/edit.twig")
     */
    public function pet_edit(Request $request, ConservationPetsRepository $conservationPetsRepository, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository, ConservationPetImageRepository $conservationPetImageRepository)
    {
        $conservationPet = $conservationPetsRepository->find($request->get('id'));
        $builder =  $this->formFactory->createBuilder(ConservationPetsType::class, $conservationPet);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $coatColor = $coatColorsRepository->find($request->get('coat_color'));
            $breedType = $breedsRepository->find($request->get('breeds_type'));
            $conservationPet->setBreedsType($breedType)
                    ->setCoatColor($coatColor);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPet);
            $entityManager->flush();

            return $this->redirectToRoute('admin_adoption_pet_list',['id'=> $request->get('id')]);
        }

        $breeds = $breedsRepository->findBy(['pet_kind' => $conservationPet->getPetKind()]);
        $colors = $coatColorsRepository->findBy(['pet_kind' => $conservationPet->getPetKind()]);
        $images = $conservationPetImageRepository->findBy(['ConservationPet' => $conservationPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE]);
        return $this->render('@admin/Adoption/pet/edit.twig', [
            'id'=>$request->get('id'),
            'conservationPet' => $conservationPet,
            'breeds' => $breeds,
            'colors' => $colors,
            'images' => $images,
            'form' => $form->createView(),
        ]);
    }
}
