<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederSearchControllerTest extends WebTestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
    }

    public function testPetSearchResult(): void
    {
        $this->client->request('GET', '/breeder/pet/search/result/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testBreederSearch(): void
    {
        $this->client->request('GET', '/breeder/breeder_search/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testBreederPetDataByPetKind(): void
    {
        $this->client->request('GET', '/breeder_pet_data_by_pet_kind/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
