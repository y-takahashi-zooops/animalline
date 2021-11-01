<?php

namespace Customize\Tests\Controller\Admin\Adoption;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/adoption/adoption_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/adoption/edit/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
