<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederMemberControllerTest extends WebTestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
    }

    public function testBreederLogin(): void
    {
        $this->client->request('GET', '/breeder/login/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testBreederMyPage(): void
    {
        $this->client->request('GET', '/breeder/member/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testBaseInfo(): void
    {
        $this->client->request('GET', '/breeder/member/baseinfo/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testTemplate(): void
    {
        $this->client->request('GET', '/breeder/member/template/not-found');
        $this->assertEquals('GET', $this->client->getRequest()->getMethod());
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
