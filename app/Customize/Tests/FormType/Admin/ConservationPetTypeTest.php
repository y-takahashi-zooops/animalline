<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\ConservationPets;
use Customize\Form\Type\Admin\ConservationPetsType;
use DateTime;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ConservationPetTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'pet_kind' => 1,
            'breeds_type' => 1,
            'pet_sex' => 1,
            'pet_birthday' => new DateTime(),
            'coat_color' => 'red',
            'future_wait' => 1,
            'dna_check_result' => 1,
            'pr_comment' => 'abc',
            'description' => 'abc',
            'delivery_time' => '14h',
            'delivery_way' => 'bus',
            'is_active' => 1,
            'release_date' => new DateTime(),
            'price' => 45346
        ];

        $object = new ConservationPets();

        $form = $this->factory->create(ConservationPetsType::class, $object);

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
