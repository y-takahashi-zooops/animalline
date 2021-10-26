<?php

namespace Customize\Tests\Controller\Admin\Breeder;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederPetControllerTest extends WebTestCase
{
    public function testSupplier(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/product/supplier/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetChangeStatus(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/breeder/pet/invalid-id/change_status');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDownloadPdf(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/breeder/pet/invalid-id/dna/download_pdf');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/breeder/pet/list/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPetEdit(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/breeder/pet/edit/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
