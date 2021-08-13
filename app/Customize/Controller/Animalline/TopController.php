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
     * TopController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     */
    public function __construct (
        ConservationPetsRepository     $conservationPetsRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
    }
    /**
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/adoption/index.twig")
     */
    public function adoption_index(Request $request)
    {
        $pets = $this->conservationPetsRepository->findBy(
            ['pet_kind' => $request->get('pet_kind') ?? AnilineConf::ANILINE_PET_KIND_DOG],
            ['release_date' => 'DESC'],
            4
        );
        return $this->render('animalline/adoption/index.twig', ['pets' => $pets]);
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
