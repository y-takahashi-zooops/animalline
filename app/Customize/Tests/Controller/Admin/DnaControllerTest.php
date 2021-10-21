<?php

namespace Customize\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DnaControllerTest extends WebTestCase
{
    public function testExaminationItem(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/dna/examination_items/not-found');
        $client->request('POST', '/%eccube_admin_route%/dna/examination_items/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBreedsByPetKind(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeds_by_pet_kind/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteExaminationItem(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/dna/examination_items/delete/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExaminationStatus(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/dna/examination_status/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDownload(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/dna/examination_status/download_pdf/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
