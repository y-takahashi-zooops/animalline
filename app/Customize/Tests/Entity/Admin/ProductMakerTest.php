<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\ProductMaker;
use PHPUnit\Framework\TestCase;

class ProductMakerTest extends TestCase
{
    public function testCreateNew(): void
    {
        $obj = new ProductMaker();
        $this->assertInstanceOf(ProductMaker::class, $obj);
    }

    public function testSetName()
    {
        $maker = new ProductMaker();
        $maker->setMakerName('name1');
        $this->assertSame('name1', $maker->getMakerName());
    }
}
