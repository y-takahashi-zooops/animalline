<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\ProductSet;
use Doctrine\Common\Collections\Collection;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use PHPUnit\Framework\TestCase;

class ProductSetTest extends TestCase
{
    public function testCreateNew(): void
    {
        $obj = new ProductSet();
        $this->assertInstanceOf(ProductSet::class, $obj);
    }

    public function testRelations(): void
    {
        $ProductSet = new ProductSet();
        $Product = new Product();
        $ProductClass = new ProductClass();

        $this->assertInstanceOf(Collection::class, $Product->getProductSet());
        $this->assertInstanceOf(Collection::class, $ProductClass->getProductSet());
        $this->assertNull($ProductSet->getProduct());
        $this->assertNull($ProductSet->getParentProduct());
        $this->assertNull($ProductSet->getProductClass());
        $this->assertNull($ProductSet->getParentProductClass());

        $this->assertNull($ProductSet->getSetUnit());
    }

    public function testSetUnit(): void
    {
        $ProductSet = new ProductSet();

        $this->assertNull($ProductSet->getSetUnit());

        $ProductSet->setSetUnit(1);
        $this->assertInternalType("int", $ProductSet->getSetUnit());
    }
}
