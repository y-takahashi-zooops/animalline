<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\InstockScheduleHeader;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class InstockScheduleHeaderTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewInstockScheduleHeader(): void
    {
        $instockScheduleHeader = new InstockScheduleHeader();
        $this->assertInstanceOf(InstockScheduleHeader::class, $instockScheduleHeader);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateInstockScheduleHeaderData(): void
    {
        $instockScheduleHeader = new InstockScheduleHeader();
        $orderDate = new DateTime();
        $supplierCode = 'code';
        $arrivalDateSchedule = new DateTime();
        $isCancel = 1;

        $instockScheduleHeader->setOrderDate($orderDate)
            ->setSupplierCode($supplierCode)
            ->setArrivalDateSchedule($arrivalDateSchedule)
            ->setIsCancel($isCancel);

        $this->assertEquals([
            $orderDate,
            $supplierCode,
            $arrivalDateSchedule,
            $isCancel,
        ],
            [
                $instockScheduleHeader->getOrderDate(),
                $instockScheduleHeader->getSupplierCode(),
                $instockScheduleHeader->getArrivalDateSchedule(),
                $instockScheduleHeader->getIsCancel(),
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
        $instockScheduleHeader = new InstockScheduleHeader();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $instockScheduleHeader->getInstockSchedule());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $instockScheduleHeader = new InstockScheduleHeader();
        $instockScheduleHeader->setOrderDate(new DateTime())
            ->setArrivalDateSchedule(new DateTime())
            ->setArrivalDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $instockScheduleHeader->getOrderDate());
        $this->assertInstanceOf(DateTime::class, $instockScheduleHeader->getArrivalDateSchedule());
        $this->assertInstanceOf(DateTime::class, $instockScheduleHeader->getArrivalDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $instockScheduleHeader = new InstockScheduleHeader();
        $integerVal = 1;
        $instockScheduleHeader->setIsCancel($integerVal)
            ->setCancelReason($integerVal);

        $this->assertEquals($integerVal, $instockScheduleHeader->getIsCancel());
        $this->assertEquals($integerVal, $instockScheduleHeader->getCancelReason());
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValues(): void
    {
        $instockScheduleHeader = new InstockScheduleHeader();

        // below fields must have a default value
        $this->assertEquals(0, $instockScheduleHeader->getIsCancel());
    }
}
