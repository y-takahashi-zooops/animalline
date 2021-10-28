<?php

namespace Customize\Tests\FormType\Adoption;

use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationsHouse;
use Customize\Form\Type\Adoption\ConservationContactType;
use Customize\Form\Type\Adoption\ConservationHouseType;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\Pref;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Validator\Validation;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;

class ConservationHouseTypeTest extends TypeTestCase
{
    private $EccubeConfig;

    protected function setUp()
    {
        $this->EccubeConfig = $this->createMock(EccubeConfig::class);

        parent::setUp();
    }

    protected function getExtensions()
    {
        $type = new ConservationHouseType($this->EccubeConfig);

        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator()),
        );
    }

    public function testSubmitValidData()
    {
        $Pref =new Pref();
        $formData = [
            'conservation_house_name' => "test",
            'conservation_house_kana' => "test",
            'conservation_house_house_zip' => "test",
            'pref' => $Pref,
            'conservation_house_city' => "test",
            'conservation_house_address' => "test",
            'conservation_house_house_tel' => "test",
            'conservation_house_house_fax' => "test",
            'conservation_house_front_name' => "test",
            'conservation_house_front_tel' => "test"
        ];

        $object = (new ConservationsHouse())
            ->setConservationHouseName("test")
            ->setConservationHouseKana("test")
            ->setConservationHouseHouseZip("test")
            ->setPref($Pref)
            ->setConservationHouseCity("test")
            ->setConservationHouseAddress("test")
            ->setConservationHouseHouseTel("test")
            ->setConservationHouseHouseFax("test")
            ->setConservationHouseFrontName("test")
            ->setConservationHouseFrontTel("test");

        $form = $this->factory->create(ConservationHouseType::class, $object);

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
