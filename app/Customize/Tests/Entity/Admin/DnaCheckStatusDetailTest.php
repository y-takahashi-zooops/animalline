<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\DnaCheckStatus;
use Customize\Entity\DnaCheckStatusDetail;
use PHPUnit\Framework\TestCase;

class DnaCheckStatusDetailTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewDnaCheckStatusDetail(): void
    {
        $dnaCheckStatusDetail = new DnaCheckStatusDetail();
        $this->assertInstanceOf(DnaCheckStatusDetail::class, $dnaCheckStatusDetail);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateDnaCheckStatusDetailData(): void
    {
        $dnaCheckStatusDetail = new DnaCheckStatusDetail();
        $CheckStatus = new DnaCheckStatus();

        $dnaCheckStatusDetail->setCheckStatus($CheckStatus);

        $this->assertEquals([
            $CheckStatus
        ],
            [
                $dnaCheckStatusDetail->getCheckStatus(),
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
        $dnaCheckStatusDetail = new DnaCheckStatusDetail();

        // Conservation contact header can have one
        $this->assertNull($dnaCheckStatusDetail->getCheckKinds());
        $this->assertNull($dnaCheckStatusDetail->getCheckStatus());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $dnaCheckStatusDetail = new DnaCheckStatusDetail();
        $integerVal = 1;
        $dnaCheckStatusDetail->setCheckResult($integerVal);

        $this->assertEquals($integerVal, $dnaCheckStatusDetail->getCheckResult());
    }
}
