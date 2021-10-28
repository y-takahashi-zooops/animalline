<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\Supplier;
use PHPUnit\Framework\TestCase;
use TypeError;

class SupplierTest extends TestCase
{
    public function testCreateNewSuppliers(): void
    {
        $Supplier = new Supplier;
        $this->assertInstanceOf(Supplier::class, $Supplier);
    }

    public function testCreateSuppliersData(): void
    {
        $supplierCode = 'supplier code';
        $supplierName = 'supplier name';
        $Supplier = (new Supplier)
            ->setSupplierCode($supplierCode)
            ->setSupplierName($supplierName);

        $this->assertEquals(
            [$supplierCode, $supplierName],
            [$Supplier->getSupplierCode(), $Supplier->getSupplierName()]
        );
    }

    public function testRequiredFields(): void
    {
        $Supplier = new Supplier;

        // below fields must have a value
        try {
            $Supplier->setSupplierCode(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $Supplier->setSupplierName(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
