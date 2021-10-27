<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederFavoriteControllerTest extends WebTestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
    }

    public function testFavoritePet(): void
    {
        $this->client->request('GET', '/breeder/pet/detail/favorite_pet/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testFavorite(): void
    {
        $this->client->request('GET', '/breeder/member/favorite/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
