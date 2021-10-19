<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederPetControllerTest extends WebTestCase
{
    public function testBreederPetList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/pet_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederPetNew(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/pets/new/invalid-barcode');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetsNewComplete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/pets/new_complete/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetsEdit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/pets/edit/invalid-id');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testPetRegistList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/pet_regist_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
