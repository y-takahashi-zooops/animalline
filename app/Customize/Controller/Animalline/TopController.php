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

class TopController extends AbstractController
{
    /**
     * @Route("/adoption/", name="adoption_top")
     * @Template("animalline/index.twig")
     */
    public function adoption_index()
    {
        return [];
    }

    /**
     * @Route("/breeder/", name="breeder_top")
     * @Template("animalline/index.twig")
     */
    public function breeder_index()
    {
        return [];
    }
}
