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

namespace Customize\Controller\Animalline;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Service\AdoptionQueryService;
use Customize\Service\BreederQueryService;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TopController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;
    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * TopController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param BreederQueryService $breederQueryService
     * @param PrefRepository $prefRepository
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository,
        BreederPetsRepository $breederPetsRepository,
        AdoptionQueryService $adoptionQueryService,
        BreederQueryService $breederQueryService,
        PrefRepository $prefRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->breederQueryService = $breederQueryService;
        $this->prefRepository = $prefRepository;
    }

    /**
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/adoption/index.twig")
     */
    public function adoption_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->adoptionQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->conservationPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['release_date' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );
        $favoritePets = $this->conservationPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['favorite_count' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );

        return $this->render('animalline/adoption/index.twig', [
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'newPets' => $newPets,
            'favoritePets' => $favoritePets,
        ]);
    }

    /**
     * @Route("/breeder/", name="breeder_top")
     * @Template("animalline/breeder/index.twig")
     */
    public function breeder_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->breederQueryService->getBreedsHavePet($petKind);
        $regions = $this->prefRepository->findAll();
        $newPets = $this->breederPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['release_date' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );
        $favoritePets = $this->breederPetsRepository->findBy(
            ['pet_kind' => $petKind],
            ['favorite_count' => 'DESC'],
            AnilineConf::NUMBER_ITEM_TOP
        );

        return $this->render('animalline/breeder/index.twig', [
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions,
            'newPets' => $newPets,
            'favoritePets' => $favoritePets,
        ]);
    }
}
