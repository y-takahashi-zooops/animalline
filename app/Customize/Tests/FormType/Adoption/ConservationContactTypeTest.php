<?php

namespace Customize\Tests\FormType\Adoption;

use Customize\Entity\ConservationContactHeader;
use Customize\Form\Type\Adoption\ConservationContactType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Validator\Validation;

class ConservationContactTypeTest extends TypeTestCase
{
    private $EccubeConfig;

    protected function setUp()
    {
        $this->EccubeConfig = $this->createMock(EccubeConfig::class);

        parent::setUp();
    }

    protected function getExtensions()
    {
        $type = new ConservationContactType($this->EccubeConfig);

        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator())
        );
    }

    public function testSubmitValidData()
    {
        $formData = [
            'contact_type' => 1,
            'contact_title' => "contact_title",
            'contact_description' => "contact_description",
            'booking_request' => "booking_request"
        ];

        $object = (new ConservationContactHeader())
            ->setContactType(1)
            ->setContactTitle("contact_title")
            ->setContactDescription("contact_description")
            ->setBookingRequest("booking_request");
        $form = $this->factory->create(ConservationContactType::class, $object);

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
