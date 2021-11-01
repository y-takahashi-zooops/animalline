<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionMemberControllerTest extends WebTestCase
{
    public function testAdoptionLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/login/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionMypage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBaseInfo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/baseinfo/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
