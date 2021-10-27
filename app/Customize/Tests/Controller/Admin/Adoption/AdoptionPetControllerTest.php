<?php

namespace Customize\Tests\Controller\Admin\Adoption;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionPetControllerTest extends WebTestCase
{
    public function testPetIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/adoption/pet/list/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetEdit(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/adoption/pet/edit/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
