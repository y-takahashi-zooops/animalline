<?php

namespace Customize\Tests\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductMakerControllerTest extends WebTestCase
{
    public function testProductMaker(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/product/maker/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
