<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\Supplier;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ProductSupplierTest extends TestCase
{
    public function testCreateNew(): void
    {
        $this->assertInstanceOf(Supplier::class, new Supplier());
    }

    public function testSetCode()
    {
        $supplier = new Supplier();
        $supplier->setSupplierCode('code1');
        $this->assertSame('code1', $supplier->getSupplierCode());
    }

    public function testSetName()
    {
        $supplier = new Supplier();
        $supplier->setSupplierName('name1');
        $this->assertSame('name1', $supplier->getSupplierName());
    }
}
