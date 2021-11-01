<?php

namespace Customize\Tests\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductInstockControllerTest extends WebTestCase
{
    public function testInstockList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/product/instock/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals('GET', $client->getRequest()->getMethod());
    }

    public function testDeleteInstock(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/product/instock/delete/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testInstockRegistration(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/instock/new/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('POST', '/%eccube_admin_route%/product/instock/edit/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
