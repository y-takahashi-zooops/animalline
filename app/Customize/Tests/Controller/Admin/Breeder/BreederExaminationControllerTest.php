<?php

namespace Customize\Tests\Controller\Admin\Breeder;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BreederExaminationControllerTest extends WebTestCase
{
    public function testExamination(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/breeder/examination/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testExaminationRegist(): void
    {
        $client = static::createClient();
        $client->request('POST', '/%eccube_admin_route%/breeder/examination/regist/invalid-id');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
