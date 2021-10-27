<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\WmsSyncInfo;
use DateTime;
use PHPUnit\Framework\TestCase;
use TypeError;

class WmsSyncInfoTest extends TestCase
{
    public function testCreateNewWmsSyncInfos(): void
    {
        $WmsSyncInfo = new WmsSyncInfo;
        $this->assertInstanceOf(WmsSyncInfo::class, $WmsSyncInfo);
    }

    public function testCreateWmsSyncInfosData(): void
    {
        $syncDate = new DateTime();
        $syncReason = 1;
        $WmsSyncInfo = (new WmsSyncInfo)
            ->setSyncDate($syncDate)
            ->setSyncResult($syncReason);

        $this->assertEquals(
            [$syncDate, $syncReason],
            [$WmsSyncInfo->getSyncDate(), $WmsSyncInfo->getSyncResult()]
        );
    }

    public function testRequiredFields(): void
    {
        $WmsSyncInfo = new WmsSyncInfo;

        // below fields must have a value
        try {
            $WmsSyncInfo->setSyncDate(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $WmsSyncInfo->setSyncResult(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
