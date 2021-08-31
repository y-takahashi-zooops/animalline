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

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\BreedersRepository;
use Customize\Config\AnilineConf;
use Knp\Component\Pager\PaginatorInterface;

class BreederController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * breederController constructor.
     *
     */
    public function __construct(
        BreedersRepository $breedersRepository
    )
    {
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/breeder_list", name="admin_breeder_list")
     * @Template("@admin/Breeder/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();
        $criteria = [];
        if (array_key_exists('breeder_name', $request)) {
            $criteria['breeder_name'] = $request['breeder_name'];
        }

        if (array_key_exists('examination_status', $request)) {
            switch ($request['examination_status']) {
                case 2:
                    $criteria['examination_status'] = [AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK, AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG];
                    break;
                case 3:
                    $criteria['examination_status'] = [AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK];
                    break;
                default:
                    break;
            }
        }

        $order = [];
        $order['field'] = array_key_exists('field', $request) ? $request['field'] : 'create_date';
        $order['direction'] = array_key_exists('direction', $request) ? $request['direction'] : 'DESC';

        $results = $this->breedersRepository->filterBreederAdmin($criteria, $order);

        $breeders = $paginator->paginate(
            $results,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $direction = 'ASC';
        if (array_key_exists('direction', $request)) {
            $direction = $request['direction'] == 'ASC' ? 'DESC' : 'ASC';
        }

        return $this->render('@admin/Breeder/index.twig', [
            'breeders' => $breeders,
            'direction' => $direction
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/edit/{id}", name="admin_breeder_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/edit.twig")
     */
    public function Edit(Request $request)
    {
        return;
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/house/{id}", name="admin_breeder_house", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/house.twig")
     */
    public function House(Request $request)
    {
        return;
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/examination/{id}", name="admin_breeder_examination", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/examination.twig")
     */
    public function Examination(Request $request)
    {
        return;
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/pet/list/{id}", name="admin_breeder_pet_list", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/index.twig")
     */
    public function pet_index(Request $request)
    {
        return;
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/pet/edit/{id}", name="admin_breeder_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/edit.twig")
     */
    public function pet_edit(Request $request)
    {
        return;
    }

}
