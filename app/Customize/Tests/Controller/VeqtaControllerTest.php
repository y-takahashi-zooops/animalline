<?php

namespace Customize\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VeqtaControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/veqta/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/veqta/pet_list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testArrive(): void
    {
        $client = static::createClient();
        $client->request('GET', '/veqta/arrive/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testGetArriveUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/veqta/arrive/get_user/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testResult(): void
    {
        $client = static::createClient();
        $client->request('GET', '/veqta/result/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testResultRegist(): void
    {
        $client = static::createClient();
        $client->request('GET', '//veqta/result_regist/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
