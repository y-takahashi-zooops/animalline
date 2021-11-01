<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionConfigurationContactControllerTest extends WebTestCase
{
    public function testAdoptionConfigrationMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/configration/message/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
