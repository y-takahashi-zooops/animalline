<?php

namespace Customize\Tests\Controller\Admin\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductWasteControllerTest extends WebTestCase
{
    public function testWaste(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/waste/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testWasteRegist(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/waste/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testSearchProduct(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/product/waste/search/product/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('POST', '/%eccube_admin_route%/product/waste/search/product/page/invalid-page_no');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
