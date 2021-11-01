<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionDnaCheckControllerTest extends WebTestCase
{
    public function testAdoptionExaminationKit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/dna_kit/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAdoptionExaminationKitNew(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/examination_status/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExaminationStatus(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/faq/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
