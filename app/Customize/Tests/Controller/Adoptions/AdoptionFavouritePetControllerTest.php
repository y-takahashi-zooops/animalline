<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionFavouritePetControllerTest extends WebTestCase
{
    public function testFavouritePet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/pet/detail/favorite_pet/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testFavourite(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/favorite/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
