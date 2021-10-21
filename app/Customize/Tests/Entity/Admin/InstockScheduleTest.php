<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\InstockSchedule;
use Eccube\Entity\ProductClass;
use PHPUnit\Framework\TestCase;

class InstockScheduleTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewInstockSchedule(): void
    {
        $instockSchedule = new InstockSchedule();
        $this->assertInstanceOf(InstockSchedule::class, $instockSchedule);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateInstockScheduleData(): void
    {
        $instockSchedule = new InstockSchedule();
        $warehouseCode = 'warehouse_code';
        $itemCode01 = 'code 1';
        $purchasePrice = 1;
        $arrivalQuantitySchedule = 1;
        $ProductClass = new ProductClass();

        $instockSchedule->setWarehouseCode($warehouseCode)
            ->setItemCode01($itemCode01)
            ->setPurchasePrice($purchasePrice)
            ->setArrivalQuantitySchedule($arrivalQuantitySchedule)
            ->setProductClass($ProductClass);

        $this->assertEquals([
            $warehouseCode,
            $itemCode01,
            $purchasePrice,
            $arrivalQuantitySchedule,
            $ProductClass,
        ],
            [
                $instockSchedule->getWarehouseCode(),
                $instockSchedule->getItemCode01(),
                $instockSchedule->getPurchasePrice(),
                $instockSchedule->getArrivalQuantitySchedule(),
                $instockSchedule->getProductClass(),
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
        $instockSchedule = new InstockSchedule();

        // Conservation contact header can have one
        $this->assertNull($instockSchedule->getProductClass());
        $this->assertNull($instockSchedule->getInstockHeader());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $instockSchedule = new InstockSchedule();
        $integerVal = 1;
        $instockSchedule->setArrivalQuantitySchedule($integerVal)
            ->setArrivalQuantity($integerVal);

        $this->assertEquals($integerVal, $instockSchedule->getArrivalQuantitySchedule());
        $this->assertEquals($integerVal, $instockSchedule->getArrivalQuantity());
    }
}
