<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionHouseControllerTest extends WebTestCase
{
    public function testHouseInfo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/member/house_info/invalid-house-id');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
