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

namespace Customize\Controller\Admin\Pet;

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
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PetController extends AbstractController
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
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * PetController constructor.
     * @param BreedsRepository $breedsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     */
    public function __construct(
        BreedsRepository          $breedsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederQueryService       $breederQueryService,
        BreederPetsRepository       $breederPetsRepository,
        ConservationPetsRepository $conservationPetsRepository
    ) {
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
    }

    /**
     * ペット情報管理
     *
     * @Route("/%eccube_admin_route%/pet", name="admin_pet_all")

     * @Template("@admin/Pet/all.twig")
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
        $criteria['holder_name'] = array_key_exists('holder_name', $request) ? $request['holder_name'] : '';

        $siteKind = $request['site_kind'] ?? '';
        $breederPets = [];
        $conservationPets = [];
        if ($siteKind == AnilineConf::ANILINE_SITE_TYPE_ADOPTION) {
            $conservationPets = $this->conservationPetsRepository->filterConservationPetsAdmin($criteria, $order);
        } elseif ($siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
            $breederPets = $this->breederPetsRepository->filterBreederPetsAdmin($criteria, $order);
        }

        $breederPets = $paginator->paginate(
            $breederPets,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $conservationPets = $paginator->paginate(
            $conservationPets,
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
            'breederPets' => $breederPets,
            'conservationPets' => $conservationPets,
        ];
    }
}
