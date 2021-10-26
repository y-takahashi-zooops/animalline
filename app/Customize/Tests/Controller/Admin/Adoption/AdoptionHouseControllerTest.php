<?php

namespace Customize\Tests\Controller\Admin\Adoption;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionHouseControllerTest extends WebTestCase
{
    public function testHouse(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/adoption/house/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
