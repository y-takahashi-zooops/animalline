<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederUploadImageControllerTest extends WebTestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
    }

    public function testMessage(): void
    {
        $this->client->request('GET', '/breeder/configration/pets/upload/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
