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

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CustomTopController extends AbstractController
{
    /**
     * @Route("/ec", name="homepage")
     * @Template("index.twig")
     */
    public function index()
    {
        return [];
    }

    /**
     * @Route("/", name="ani_homepage")
     * @Template("ani_index.twig")
     */
    public function animalline_index()
    {
        return [];
    }

    /**
     * @Route("/policy", name="ani_policy")
     * @Template("ani_policy.twig")
     */
    public function policy()
    {
        return [];
    }

    /**
     * @Route("/terms", name="ani_terms")
     * @Template("ani_terms.twig")
     */
    public function terms()
    {
        return [];
    }

    /**
     * @Route("/company", name="ani_company")
     * @Template("ani_company.twig")
     */
    public function company()
    {
        return [];
    }

    /**
     * @Route("/contact", name="ani_contact")
     * @Template("ani_contact.twig")
     */
    public function contact()
    {
        return [];
    }
}
