<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\DnaCheckStatus;
use Customize\Entity\DnaCheckStatusHeader;
use Customize\Entity\ReturnScheduleHeader;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Eccube\Entity\Order;
use PHPUnit\Framework\TestCase;

class ReturnScheduleHeaderTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewReturnScheduleHeader(): void
    {
        $returnScheduleHeader = new ReturnScheduleHeader();
        $this->assertInstanceOf(ReturnScheduleHeader::class, $returnScheduleHeader);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateReturnScheduleHeaderData(): void
    {
        $returnScheduleHeader = new ReturnScheduleHeader();
        $Order = new Order();
        $returnDateSchedule = new DateTime();
        $customerName = "name";
        $customerZip = "customer zip";
        $customerAddress = "address";
        $customerTel = "tel";

        $returnScheduleHeader->setOrder($Order)
            ->setReturnDateSchedule($returnDateSchedule)
            ->setCustomerName($customerName)
            ->setCustomerZip($customerZip)
            ->setCustomerAddress($customerAddress)
            ->setCustomerTel($customerTel);

        $this->assertEquals([
            $Order,
            $returnDateSchedule,
            $customerName,
            $customerZip,
            $customerAddress,
            $customerTel
        ],
            [
                $returnScheduleHeader->getOrder(),
                $returnScheduleHeader->getReturnDateSchedule(),
                $returnScheduleHeader->getCustomerName(),
                $returnScheduleHeader->getCustomerZip(),
                $returnScheduleHeader->getCustomerAddress(),
                $returnScheduleHeader->getCustomerTel(),
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
        $returnScheduleHeader = new ReturnScheduleHeader();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $returnScheduleHeader->getReturnDateSchedule());

        // Conservation contact header can have one
        $this->assertNull($returnScheduleHeader->getOrder());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $returnScheduleHeader = new ReturnScheduleHeader();

        $returnScheduleHeader->setReturnDateSchedule(new DateTime())
            ->setReturnDate(new DateTime())
            ->setWmsReciveDate(new DateTime())
            ->setWmsSendDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $returnScheduleHeader->getReturnDateSchedule());
        $this->assertInstanceOf(DateTime::class, $returnScheduleHeader->getReturnDate());
        $this->assertInstanceOf(DateTime::class, $returnScheduleHeader->getWmsReciveDate());
        $this->assertInstanceOf(DateTime::class, $returnScheduleHeader->getWmsSendDate());
    }
}
