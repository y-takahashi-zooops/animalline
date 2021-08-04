<?php

namespace Customize\Controller\Test;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppTestController extends AbstractController
{
    /**
     * @Route("/test/list_adoption", name="test_crud_adoption")
     * @Template("test/alm_adoption.twig")
     */
    public function index()
    {
        return [];
    }
}
