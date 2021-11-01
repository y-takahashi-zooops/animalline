<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\StockWasteReason;
use PHPUnit\Framework\TestCase;

class ProductWasteReasonTest extends TestCase
{
    public function testCreateNew(): void
    {
        $obj = new StockWasteReason();
        $this->assertInstanceOf(StockWasteReason::class, $obj);
    }

    public function testSetReason()
    {
        $maker = new StockWasteReason();
        $maker->setWasteReason('name1');
        $this->assertSame('name1', $maker->getWasteReason());
    }
}
