<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\DnaCheckStatusHeader;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class DnaCheckStatusHeaderTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewDnaCheckStatusHeader(): void
    {
        $dnaCheckStatusHeader = new DnaCheckStatusHeader();
        $this->assertInstanceOf(DnaCheckStatusHeader::class, $dnaCheckStatusHeader);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateDnaCheckStatusHeaderData(): void
    {
        $dnaCheckStatusHeader = new DnaCheckStatusHeader();
        $registerId = 15;
        $siteType = 1;
        $shippingStatus = 1;

        $dnaCheckStatusHeader->setRegisterId($registerId)
            ->setSiteType($siteType)
            ->setShippingStatus($shippingStatus);


        $this->assertEquals([
            $registerId,
            $siteType,
            $shippingStatus,
        ],
            [
                $dnaCheckStatusHeader->getRegisterId(),
                $dnaCheckStatusHeader->getSiteType(),
                $dnaCheckStatusHeader->getShippingStatus(),
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
        $dnaCheckStatusHeader = new DnaCheckStatusHeader();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $dnaCheckStatusHeader->getDnaCheckStatus());

        // Conservation contact header can have one
        $this->assertNull($dnaCheckStatusHeader->getPrefShipping());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $dnaCheckStatusHeader = new DnaCheckStatusHeader();

        $dnaCheckStatusHeader->setKitShippingDate(new DateTime())
            ->setKitShippingOperationDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $dnaCheckStatusHeader->getKitShippingDate());
        $this->assertInstanceOf(DateTime::class, $dnaCheckStatusHeader->getKitShippingOperationDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $dnaCheckStatusHeader = new DnaCheckStatusHeader();
        $integerVal = 1;
        $dnaCheckStatusHeader->setRegisterId($integerVal)
            ->setPetId($integerVal)
            ->setSiteType($integerVal)
            ->setShippingStatus($integerVal)
            ->setKitUnit($integerVal);

        $this->assertEquals($integerVal, $dnaCheckStatusHeader->getRegisterId());
        $this->assertEquals($integerVal, $dnaCheckStatusHeader->getPetId());
        $this->assertEquals($integerVal, $dnaCheckStatusHeader->getSiteType());
        $this->assertEquals($integerVal, $dnaCheckStatusHeader->getShippingStatus());
        $this->assertEquals($integerVal, $dnaCheckStatusHeader->getKitUnit());
    }
}
