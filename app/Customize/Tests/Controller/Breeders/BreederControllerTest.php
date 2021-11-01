<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederControllerTest extends WebTestCase
{
    public function testBreederReg(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/reg/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederGuideDog(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/guide/dog/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederGuideCat(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/guide/cat/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederMyPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederPetDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/pet/detail/invalid-pet-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testFavoritePet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/pet/detail/favorite_pet/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/breeder_search/invalid-breeder-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testCompany(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/company/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testTradeLaw(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/tradelaw/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPolicy(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/policy/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testTerms(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/terms/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAniContact(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/ani_contact/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
