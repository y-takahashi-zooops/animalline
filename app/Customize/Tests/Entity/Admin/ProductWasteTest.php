<?php

namespace Customize\Tests\Entity\Admin;

use Carbon\Carbon;
use Customize\Entity\StockWaste;
use Customize\Entity\StockWasteReason;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use PHPUnit\Framework\TestCase;

class ProductWasteTest extends TestCase
{
    public function testCreateNew(): void
    {
        $obj = new StockWaste();
        $this->assertInstanceOf(StockWaste::class, $obj);
    }

    public function testRelations(): void
    {
        $Waste = new StockWaste();

        $this->assertNull($Waste->getProduct());
        $this->assertNull($Waste->getProductClass());
        $this->assertNull($Waste->getStockWasteReason());
    }

    public function testSetData()
    {
        $Waste = new StockWaste();
        $Product = new Product();
        $ProductClass = new ProductClass();
        $Reason = new StockWasteReason();
        $date = Carbon::now();

        $Waste->setProduct($Product)
            ->setProductClass($ProductClass)
            ->setWasteDate($date)
            ->setWasteUnit(1)
            ->setStockWasteReason($Reason)
            ->setWasteUnit(1);

        $this->assertEquals(
            [$Product, $ProductClass, $date, 1, $Reason, 1],
            [
                $Waste->getProduct(),
                $Waste->getProductClass(),
                $Waste->getWasteDate(),
                $Waste->getWasteUnit(),
                $Waste->getStockWasteReason(),
                $Waste->getWasteUnit()
            ]
        );
    }
}
