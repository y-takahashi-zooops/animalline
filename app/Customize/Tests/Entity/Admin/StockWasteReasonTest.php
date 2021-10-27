<?php

namespace Customize\Tests\Entity;

use Customize\Entity\StockWasteReason;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class StockWasteReasonTest extends TestCase
{
    public function testCreateNewStockWasteReason(): void
    {
        $StockWasteReason = new StockWasteReason;
        $this->assertInstanceOf(StockWasteReason::class, $StockWasteReason);
    }

    public function testCreateStockWasteReasonData(): void
    {
        $wasteReason = 'waste reason';
        $StockWasteReason = (new StockWasteReason)
            ->setWasteReason($wasteReason);

        $this->assertEquals(
            $wasteReason,
            $StockWasteReason->getWasteReason()
        );
    }

    public function testRelations(): void
    {
        $StockWasteReason = new StockWasteReason;

        // stock waste reason has many stock wastes
        $this->assertInstanceOf(Collection::class, $StockWasteReason->getStockWaste());
        $this->assertNotNull($StockWasteReason->getStockWaste());
    }
}
