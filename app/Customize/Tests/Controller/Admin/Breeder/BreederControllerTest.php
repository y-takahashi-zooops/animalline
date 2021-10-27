<?php

namespace Customize\Tests\Controller\Admin\Breeder;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/breeder/breeder_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/breeder/edit/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
