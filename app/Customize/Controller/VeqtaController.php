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
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;

class VeqtaController extends AbstractController
{
    /**
     * @Route("/veqta/", name="veqta_index")
     * @Template("animalline/veqta/index.twig")
     */
    public function index()
    {
        return [];
    }

    /**
     * @Route("/veqta/arrive", name="veqta_arrive")
     * @Template("animalline/veqta/arrive.twig")
     */
    public function arrive()
    {
        return [];
    }

    /**
     * @Route("/veqta/result", name="veqta_result")
     * @Template("animalline/veqta/result.twig")
     */
    public function result()
    {
        return [];
    }
}
