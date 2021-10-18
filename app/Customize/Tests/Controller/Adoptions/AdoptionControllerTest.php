<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionsControllerTest extends WebTestCase
{
    public function testAdoptionIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionMyPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionFaq(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/faq/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionViewhist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/viewhist/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionReadhist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/readfirst/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/list/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/adoption_search/invalid-adoption-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testCompany(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/company/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testTradeLaw(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/tradelaw/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPolicy(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/policy/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testTerms(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/terms/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAniContact(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/ani_contact/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
