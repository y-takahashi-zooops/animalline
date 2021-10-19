<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionPetsControllerTest extends WebTestCase
{
    public function testAdoptionPetsNew(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/pets/new/invalid-barcode');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionPetsNewComplete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/adoption/pets/new_complete/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionPetsEdit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/pets/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionPetList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/pet_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/pet/detail/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetRegistList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/pet_regist_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
