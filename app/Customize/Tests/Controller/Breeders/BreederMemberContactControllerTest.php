<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederMemberContactControllerTest extends WebTestCase
{
    public function testAllMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/all_message/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/message/{id}');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/message/{id}',
            ['id' => 1]
        );
        $this->assertEquals('id=1', $client->getRequest()->getQueryString());
    }

    public function testContract(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/contract/{pet_id}');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/contract/{pet_id}',
            ['pet_id' => 1]
        );
        $this->assertEquals('pet_id=1', $client->getRequest()->getQueryString());
    }

    public function testContractComplete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/contract/complete/{pet_id}');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/contract/complete/{pet_id}',
            ['pet_id' => 1]
        );
        $this->assertEquals('pet_id=1', $client->getRequest()->getQueryString());
    }

    public function testAllBreederMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/all_breeder_message/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreederMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/breeder_message/{id}');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/breeder_message/{id}',
            ['id' => 1]
        );
        $this->assertEquals('id=1', $client->getRequest()->getQueryString());
    }

    public function testContact(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/contact/{pet_id}');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/contact/{pet_id}',
            ['pet_id' => 1]
        );
        $this->assertEquals('pet_id=1', $client->getRequest()->getQueryString());
    }

    public function testComplete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/contact/{pet_id}/complete');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/contact/{pet_id}/complete',
            ['pet_id' => 1]
        );
        $this->assertEquals('pet_id=1', $client->getRequest()->getQueryString());
    }
}
