<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederDnaCheckControllerTest extends WebTestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
    }

    public function testBreederExaminationKit(): void
    {
        $this->client->request('GET', '/breeder/member/dna_kit/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

        $this->client->request('POST', '/breeder/member/dna_kit/not-found');
        $this->assertEquals('POST', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testBreederExaminationKitNew(): void
    {
        $this->client->request('GET', '/breeder/member/dna_kit/new/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

        $this->client->request('POST', '/breeder/member/dna_kit/new/not-found');
        $this->assertEquals('POST', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
