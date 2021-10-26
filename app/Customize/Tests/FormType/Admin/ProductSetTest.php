<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\ProductSet;
use Customize\Form\Type\Admin\ProductSetType;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Form\Type\Admin\OrderItemType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductSetTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $OrderItemType = $this->factory->create(OrderItemType::class);
        $OrderItem = (new OrderItem())
                    ->setProductName('abc')
                    ->setProductCode('100134');
                    

        $formData = [
            'ProductSet' => [
                'entry_type' => $OrderItem,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true
            ],
            'ProductSetErrors' => 'abc'
        ];

        $form = $this->factory->create(ProductSetType::class);

        $object = (new ProductSet())
                    ->setParentProduct(new Product)
                    ->setParentProductClass(new ProductClass)
                    ->setProduct(new Product)
                    ->setProductClass(new ProductClass)
                    ->setSetUnit(1);

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
