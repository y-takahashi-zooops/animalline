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
use Customize\Repository\ConservationsRepository;
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
        $criterial = [];
        if (isset($request['organization_name']) && !empty($request['organization_name'])) {
            $criterial['organization_name'] = $request['organization_name'];
        }

        if (isset($request['examination_status'])) {
            switch ($request['examination_status']) {
                case 1:
                    break;
                case 2:
                    $criterial['examination_status'] = [1, 2];
                    break;
                case 3:
                    $criterial['examination_status'] = 0;
                    break;
            }
        }

        $order = isset($request['ordering']) ? [$request['ordering'] => 'DESC'] : ['create_date' => 'DESC'];
        $results = $this->conservationsRepository->findBy($criterial, $order);
        $conservations = $paginator->paginate(
            $results,
            $request['page'] ?? 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('@admin/Adoption/index.twig', [
            'conservations' => $conservations,
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/adoption/edit/{id}", name="admin_adoption_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/edit.twig")
     */
    public function Edit(Request $request)
    {
        return;
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
    public function pet_edit(Request $request)
    {
        return;
    }

}
