<?php

namespace Eccube\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AniconfigController extends AbstractController
{
    /**
     * @Route("/aniconfig", name="aniconfig_index")
     */
    public function index(): Response
    {
        // 必要に応じて処理を書く
        return new Response('Aniconfig index page');
    }
}
