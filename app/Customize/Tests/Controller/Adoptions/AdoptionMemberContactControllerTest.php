<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionMemberContactControllerTest extends WebTestCase
{
    public function testAllMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/all_message/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/message/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testContact(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/contact/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testComplete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/contact/invalid-id/complete');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionMessageContract(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/message/invalid-id/contract');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionMessageCancel(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/message/invalid-id/cancel');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
