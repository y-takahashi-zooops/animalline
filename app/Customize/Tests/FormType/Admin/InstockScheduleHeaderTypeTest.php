<?php

namespace Customize\Tests\FormType\Admin;

use Carbon\Carbon;
use Customize\Entity\InstockScheduleHeader;
use Customize\Form\Type\Admin\InstockScheduleHeaderType;
use Customize\Repository\SupplierRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;
use DateTime;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\OrderItem;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class InstockScheduleHeaderTypeTest extends TypeTestCase
{
    private $entityManager;
    private $eventDispatcher;
    private $supplierRepository;

    protected function setUp()
    {
        $this->supplierRepository = $this->createMock(SupplierRepository::class);
        parent::setUp();
    }

    public function testSubmitValidData()
    {
        $date = new DateTime();
        $orderItem = new OrderItem;
        $formData = [
            'order_date' => $date,
            'supplier_code' => 1,
            'arrival_date_schedule' => $date,
            'remark_text' => 'abc',
            'InstockSchedule' => [$orderItem]
        ];

        $object = (new InstockScheduleHeader())
                    ->setOrderDate($date)
                    ->setSupplierCode(1)
                    ->setArrivalDateSchedule($date)
                    ->setRemarkText('abc')
                    ->setInstockSchedule(OrderItem::class);

        $form = $this->factory->create(InstockScheduleHeaderType::class);

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
        $type = new InstockScheduleHeaderType($this->supplierRepository);

        return array(
            new PreloadedExtension(array($type), array()),
        );
    }
}
