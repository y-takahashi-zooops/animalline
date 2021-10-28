<?php

namespace Customize\Tests\FormType\Breeder;

use Customize\Entity\BreederPetinfoTemplate;
use Customize\Form\Type\Breeder\BreederPetinfoTemplateType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class BreederPetinfoTemplateTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'guarantee' => 'test',
            'delivery_way' => 'test',
            'payment_method' => 'test',
            'reservation_fee' => 'test',
        ];

        $object = new BreederPetinfoTemplate();

        $form = $this->factory->create(BreederPetinfoTemplateType::class, $object);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }
}
