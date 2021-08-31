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

class BreederController extends AbstractController
{
    /**
     * breederController constructor.
     *
     */
    public function __construct(
    ) {
    }

    /**
     * @Route("/%eccube_admin_route%/breeder/breeder_list", name="admin_breeder_list")
     * @Template("@admin/Breeder/index.twig")
     */
    public function index(Request $request)
    {
        return;
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
     * @Route("/%eccube_admin_route%/breeder/pet/edit/{id}", name="admin_breeder_pet_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/pet/edit.twig")
     */
    public function pet_edit(Request $request)
    {
        return;
    }

}
