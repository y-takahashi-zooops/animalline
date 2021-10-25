<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionSearchControllerTest extends WebTestCase
{
    public function testAdoptionSearch(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/adoption_search/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetSearchResult(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/pet/search/result/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetDataByPetKind(): void
    {
        $client = static::createClient();
        $client->request('GET', '/pet_data_by_pet_kind/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
