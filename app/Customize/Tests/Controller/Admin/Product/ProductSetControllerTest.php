<?php

namespace Customize\Tests\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductSetControllerTest extends WebTestCase
{
    public function testProductSet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/product/set/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
