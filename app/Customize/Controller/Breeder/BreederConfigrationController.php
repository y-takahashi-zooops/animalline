<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class BreederConfigrationController extends AbstractController
{

    /**
     * BreederConfigrationController constructor.
     */
    public function __construct(
        
    )
    {
        return;
    }

    /**
     * ブリーダー管理ページTOP
     *
     * @Route("/breeder/configration/", name="breeder_configration")
     * @Template("animalline/breeder/configration/index.twig")
     */
    public function breeder_configration(Request $request)
    {
        return;
    }
}