<?php

namespace Customize\Controller;

use Symfony\Component\HttpFoundation\Response;
# use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
    * @Route("/test-route", name="test_route")
    */	
    public function test(): Response
    {
        return new Response('It works!');
    }
}
