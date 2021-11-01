<?php

namespace Customize\Tests\Controller\Breeders;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederExamControllerTest extends WebTestCase
{
    public function testExamination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/examination/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExaminationInfo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/examination_info/{pet-type}/not-found');
        $this->assertEquals('GET', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('POST', '/breeder/member/examination_info/{pet-type}/not-found');
        $this->assertEquals('POST', $client->getRequest()->getMethod());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request(
            'GET',
            '/breeder/member/examination_info/{pet_type}',
            ['pet_type' => 1]
        );
        $this->assertEquals('pet_type=1', $client->getRequest()->getQueryString());
    }

    public function testExaminationSubmit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/examination/submit/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExaminationStatus(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder/member/examination_status');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testBreederPedigreeDataByPetKind(): void
    {
        $client = static::createClient();
        $client->request('GET', '/breeder_pedigree_data_by_pet_kind/not-found');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
