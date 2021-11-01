<?php

namespace Customize\Tests\Entity;

use Customize\Entity\StockWaste;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use PHPUnit\Framework\TestCase;
use TypeError;

class StockWasteTest extends TestCase
{
    public function testCreateNewStockWaste(): void
    {
        $StockWaste = new StockWaste;
        $this->assertInstanceOf(StockWaste::class, $StockWaste);
    }

    public function testCreateStockWasteData(): void
    {
        $ProductClass = new ProductClass;
        $StockWaste = (new StockWaste)
            ->setProductClass($ProductClass);

        $this->assertEquals(
            $ProductClass,
            $StockWaste->getProductClass()
        );
    }

    public function testRelations(): void
    {
        $StockWaste = (new StockWaste)
            ->setProduct(new Product)
            ->setProductClass(new ProductClass);

        // stock waste belongs to a product, product class
        $this->assertInstanceOf(Product::class, $StockWaste->getProduct());
        $this->assertInstanceOf(ProductClass::class, $StockWaste->getProductClass());
        $this->assertNotNull($StockWaste->getProduct());
        $this->assertNotNull($StockWaste->getProductClass());
        $this->expectException(TypeError::class);
        $StockWaste->setProductClass(null);
    }

    public function testRequiredFields(): void
    {
        $StockWaste = new StockWaste;

        // below fields must have a value
        try {
            $StockWaste->setProductClass(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
