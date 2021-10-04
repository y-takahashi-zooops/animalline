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
use Customize\Repository\CoatColorsRepository;
use Knp\Component\Pager\PaginatorInterface;

class BreederPetController extends AbstractController
{
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
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * BreederPetController constructor.
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     */
    public function __construct(
        BreedsRepository                 $breedsRepository,
        CoatColorsRepository             $coatColorsRepository,
        BreederPetImageRepository        $breederPetImageRepository,
        BreederQueryService              $breederQueryService
    )
    {
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->coatColorsRepository = $coatColorsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
    }

    /**
     * ペット一覧ブリーダー管理
     *
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
     * ペット情報編集ブリーダー管理
     *
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
