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
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\BreedsRepository;
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
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * TopController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param BreedsRepository $breedsRepository
     * @param PrefRepository $prefRepository
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository,
        BreedsRepository           $breedsRepository,
        PrefRepository             $prefRepository
    )
    {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->prefRepository = $prefRepository;
    }

    /**
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/adoption/index.twig")
     */
    public function adoption_index(Request $request)
    {
        $petKind = $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG;
        $breeds = $this->breedsRepository->findBy(['pet_kind' => $petKind]);
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
    public function breeder_index()
    {
        return [];
    }
}
