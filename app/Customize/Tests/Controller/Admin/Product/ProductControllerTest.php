<?php

namespace Customize\Tests\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('POST', '/%eccube_admin_route%/product/page/invalid-page_no');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testLoadProductClasses(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/classes/invalid-id/load');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAddImage(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/product/image/add/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/product/new/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('POST', '/%eccube_admin_route%/product/product/invalid-id/edit');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetEdit(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/breeder/pet/edit/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/%eccube_admin_route%/product/product/invalid-id/delete');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals('DELETE', $client->getRequest()->getMethod());
    }

    public function testCopy(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/product/invalid-id/copy');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals('POST', $client->getRequest()->getMethod());
    }

    public function testDisplay(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/product/invalid-id/display');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExport(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/export/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBulkProductStatus(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/bulk/product-status/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
