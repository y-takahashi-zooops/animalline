<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\ReturnSchedule;
use Customize\Entity\ReturnScheduleHeader;
use Eccube\Entity\OrderItem;
use Eccube\Entity\ProductClass;
use PHPUnit\Framework\TestCase;

class ReturnScheduleTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewReturnSchedule(): void
    {
        $returnSchedule = new ReturnSchedule();
        $this->assertInstanceOf(ReturnSchedule::class, $returnSchedule);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateReturnScheduleData(): void
    {
        $returnSchedule = new ReturnSchedule();
        $ReturnScheduleHeader = new ReturnScheduleHeader();
        $ProductClass = new ProductClass();
        $warehouseCode = "warehouse_code";
        $itemCode01 = "item_code_01";
        $quantitySchedule = 1;
        $standerdPrice = 1;
        $sellingPrice = 1;
        $OrderItem = new OrderItem();

        $returnSchedule->setReturnScheduleHeader()
            ->setProductClass($ProductClass)
            ->setWarehouseCode($warehouseCode)
            ->setItemCode01($itemCode01)
            ->setQuantitySchedule($quantitySchedule)
            ->setStanderdPrice($standerdPrice)
            ->setSellingPrice($sellingPrice)
            ->setOrderItem($OrderItem);

        $this->assertEquals([
            $ReturnScheduleHeader,
            $ProductClass,
            $warehouseCode,
            $itemCode01,
            $quantitySchedule,
            $standerdPrice,
            $sellingPrice,
            $OrderItem
        ],
            [
                $returnSchedule->getReturnScheduleHeader(),
                $returnSchedule->getProductClass(),
                $returnSchedule->getWarehouseCode(),
                $returnSchedule->getItemCode01(),
                $returnSchedule->getQuantitySchedule(),
                $returnSchedule->getStanderdPrice(),
                $returnSchedule->getSellingPrice(),
                $returnSchedule->getOrderItem(),
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
        $returnSchedule = new ReturnSchedule();

        // Conservation contact header can have one
        $this->assertNull($returnSchedule->getReturnScheduleHeader());
        $this->assertNull($returnSchedule->getProductClass());
        $this->assertNull($returnSchedule->getOrderItem());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $returnSchedule = new ReturnSchedule();
        $integerVal = 1;
        $returnSchedule->setQuantitySchedule($integerVal)
            ->setQuantity($integerVal)
            ->setStanderdPrice($integerVal)
            ->setSellingPrice($integerVal);

        $this->assertEquals($integerVal, $returnSchedule->getQuantitySchedule());
        $this->assertEquals($integerVal, $returnSchedule->getQuantity());
        $this->assertEquals($integerVal, $returnSchedule->getStanderdPrice());
        $this->assertEquals($integerVal, $returnSchedule->getSellingPrice());
    }
}
