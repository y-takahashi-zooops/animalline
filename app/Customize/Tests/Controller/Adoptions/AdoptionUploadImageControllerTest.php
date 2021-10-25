<?php

namespace Customize\Tests\Controller\Adoptions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionUploadImageControllerTest extends WebTestCase
{
    public function testUpload(): void
    {
        $client = static::createClient();
        $client->request('GET', '/adoption/configration/pets/upload/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
