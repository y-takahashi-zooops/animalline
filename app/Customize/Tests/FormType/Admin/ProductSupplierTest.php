<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\Supplier;
use Customize\Form\Type\Admin\SupplierType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductSupplierTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'supplier_name' => 'test1',
            'supplier_code' => 'code',
        ];

        $object = new Supplier();

        $form = $this->factory->create(SupplierType::class, $object);

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