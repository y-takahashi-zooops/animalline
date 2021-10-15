<?php

namespace Customize\Tests\FormType;

use Customize\Entity\ProductMaker;
use Customize\Form\Type\Admin\ProductMakerType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class AdminProductMakerTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'maker_name' => 'test',
        ];

        $object = new ProductMaker();

        $form = $this->factory->create(ProductMakerType::class, $object);

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
