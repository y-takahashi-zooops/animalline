<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\StockWaste;
use Customize\Entity\StockWasteReason;
use Customize\Form\Type\Admin\StockWasteType;
use DateTime;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class StockWasteTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $stockWasteReason = new StockWasteReason();
        $formData = [
            'waste_date' => new DateTime(),
            'waste_unit' => 1,
            'stock_waste_reason' => new StockWasteReason(),
            'comment' => 'abc'
        ];

        $object = new StockWaste();

        $form = $this->factory->create(StockWasteType::class, $object);

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