<?php

namespace Customize\Tests\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductSupplierControllerTest extends WebTestCase
{
    public function testSupplier(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/supplier/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
