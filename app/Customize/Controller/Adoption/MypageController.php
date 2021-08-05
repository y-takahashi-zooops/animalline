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

namespace Customize\Controller\Adoption;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class MypageController extends AbstractController
{
     /**
     * MypageController constructor.
     *
     * @param 
     */
    public function __construct(
    ) {
    }

    /**
     * マイページ.
     *
     * @Route("/adoption/configration/mypage", name="adoption_mypage")
     * @Template("animalline/adoption/mypage/mypage.twig")
     */
    public function index(Request $request)
    {
        return;
    }
}
