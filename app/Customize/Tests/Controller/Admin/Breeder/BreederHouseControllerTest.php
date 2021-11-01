<?php

namespace Customize\Tests\Controller\Admin\Breeder;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederHouseControllerTest extends WebTestCase
{
    public function testHouse(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/breeder/house/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
