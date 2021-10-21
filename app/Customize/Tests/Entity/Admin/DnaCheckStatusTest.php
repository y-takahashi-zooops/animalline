<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\DnaCheckStatus;
use Customize\Entity\DnaCheckStatusHeader;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class DnaCheckStatusTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewDnaCheckStatus(): void
    {
        $dnaCheckStatus = new DnaCheckStatus();
        $this->assertInstanceOf(DnaCheckStatus::class, $dnaCheckStatus);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateDnaCheckStatusData(): void
    {
        $dnaCheckStatus = new DnaCheckStatus();
        $DnaHeader = new DnaCheckStatusHeader();
        $siteType = 1;
        $checkStatus = 1;

        $dnaCheckStatus->setDnaHeader($DnaHeader)
            ->setSiteType($siteType)
            ->setCheckStatus($checkStatus);


        $this->assertEquals([
            $DnaHeader,
            $siteType,
            $checkStatus,
        ],
            [
                $dnaCheckStatus->getDnaHeader(),
                $dnaCheckStatus->getSiteType(),
                $dnaCheckStatus->getCheckStatus(),
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
        $dnaCheckStatus = new DnaCheckStatus();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $dnaCheckStatus->getCheckStatusDetails());

        // Conservation contact header can have one
        $this->assertNull($dnaCheckStatus->getDnaHeader());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $dnaCheckStatus = new DnaCheckStatus();
        $dnaCheckStatus->setKitPetRegisterDate(new DateTime())
            ->setKitReturnDate(new DateTime())
            ->setCheckReturnDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $dnaCheckStatus->getKitPetRegisterDate());
        $this->assertInstanceOf(DateTime::class, $dnaCheckStatus->getKitReturnDate());
        $this->assertInstanceOf(DateTime::class, $dnaCheckStatus->getCheckReturnDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $dnaCheckStatus = new DnaCheckStatus();
        $integerVal = 1;
        $dnaCheckStatus->setPetId($integerVal)
            ->setSiteType($integerVal)
            ->setCheckStatus($integerVal);

        $this->assertEquals($integerVal, $dnaCheckStatus->getPetId());
        $this->assertEquals($integerVal, $dnaCheckStatus->getSiteType());
        $this->assertEquals($integerVal, $dnaCheckStatus->getCheckStatus());
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValues(): void
    {
        $dnaCheckStatus = new DnaCheckStatus();

        // below fields must have a default value
        $this->assertEquals(1, $dnaCheckStatus->getCheckStatus());
    }
}
