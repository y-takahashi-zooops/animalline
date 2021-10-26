<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederHouseControllerTest extends WebTestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
    }

    public function testMessage(): void
    {
        $this->client->request('GET', '/breeder/member/house_info/{pet_type}/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->client->request(
            'GET',
            '/breeder/member/house_info/{pet_type}',
            ['pet_type' => 1]
        );
        $this->assertEquals('pet_type=1', $this->client->getRequest()->getQueryString());
    }
}
