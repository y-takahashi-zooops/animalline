<?php

namespace Customize\Tests\Controller\Admin\Adoption;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdoptionExaminationControllerTest extends WebTestCase
{
    public function testExamination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/%eccube_admin_route%/adoption/examination/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExaminationRegist(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/adoption/examination/regist/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
