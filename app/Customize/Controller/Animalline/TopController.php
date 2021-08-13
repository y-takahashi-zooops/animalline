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

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\BreedsRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Config\AnilineConf;

class TopController extends AbstractController
{
    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * AdoptionController constructor.
     *
     * @param BreedsRepository $breedsRepository\
     * @param PrefRepository $prefRepository
     */

    public function __construct(
        BreedsRepository $breedsRepository,
        PrefRepository $prefRepository
    ) {
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

        $breeds = $this->breedsRepository->findAll();
        $regions = $this->prefRepository->findAll();

        return $this->render('animalline/adoption/index.twig', [
            'petKind' => $petKind,
            'breeds' => $breeds,
            'regions' => $regions
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
