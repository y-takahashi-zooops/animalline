<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\DnaCheckStatus;
use Customize\Entity\DnaCheckStatusHeader;
use Customize\Entity\ShippingSchedule;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Eccube\Entity\OrderItem;
use PHPUnit\Framework\TestCase;

class ShippingScheduleTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewShippingSchedule(): void
    {
        $shippingSchedule = new ShippingSchedule();
        $this->assertInstanceOf(ShippingSchedule::class, $shippingSchedule);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateShippingScheduleData(): void
    {
        $shippingSchedule = new ShippingSchedule();

        $quantity = "quantity";
        $standerdPrice = 1;
        $sellingPrice = 1;
        $OrderDetail = new OrderItem();

        $shippingSchedule->setQuantity($quantity)
            ->setStanderdPrice($standerdPrice)
            ->setSellingPrice($sellingPrice)
            ->setOrderDetail($OrderDetail);


        $this->assertEquals([
            $quantity,
            $standerdPrice,
            $sellingPrice,
            $OrderDetail,
        ],
            [
                $shippingSchedule->getQuantity(),
                $shippingSchedule->getStanderdPrice(),
                $shippingSchedule->getSellingPrice(),
                $shippingSchedule->getOrderDetail(),
            ]
        );
    }

    /**
     * Test relation
     *
     * @return void
     */
    public function testRelations(): void
    {
        $shippingSchedule = new ShippingSchedule();

        // Conservation contact header can have one
        $this->assertNull($shippingSchedule->getOrderDetail());
        $this->assertNull($shippingSchedule->getProductClass());
        $this->assertNull($shippingSchedule->getShippingScheduleHeader());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $shippingSchedule = new ShippingSchedule();

        $integerVal = 1;
        $shippingSchedule->setStanderdPrice($integerVal)
            ->setSellingPrice($integerVal);

        $this->assertEquals($integerVal, $shippingSchedule->getStanderdPrice());
        $this->assertEquals($integerVal, $shippingSchedule->getSellingPrice());
    }
}
