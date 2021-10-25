<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\ShippingScheduleHeader;
use Eccube\Entity\Shipping;
use PHPUnit\Framework\TestCase;
use DateTime;
use Doctrine\Common\Collections\Collection;

class ShippingScheduleHeaderTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewShippingScheduleHeader(): void
    {
        $shippingScheduleHeader = new ShippingScheduleHeader();
        $this->assertInstanceOf(ShippingScheduleHeader::class, $shippingScheduleHeader);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateShippingScheduleHeaderData(): void
    {
        $shippingScheduleHeader = new ShippingScheduleHeader();

        $shippingDateSchedule = new DateTime();
        $arrivalDateSchedule = new DateTime();
        $customerName = "customer_name";
        $customerZip = "customer_zip";
        $customerAddress = "customer_address";
        $customerTel = "customer_tel";
        $totalPrice = 1;
        $discountedPrice = 1;
        $taxPrice = 1;
        $postagePrice = 1;
        $totalWeight = 1;
        $isCancel = 0;
        $Shipping = new Shipping();

        $shippingScheduleHeader->setShippingDateSchedule($shippingDateSchedule)
            ->setArrivalDateSchedule($arrivalDateSchedule)
            ->setCustomerName($customerName)
            ->setCustomerZip($customerZip)
            ->setCustomerAddress($customerAddress)
            ->setCustomerTel($customerTel)
            ->setTotalPrice($totalPrice)
            ->setDiscountedPrice($discountedPrice)
            ->setTaxPrice($taxPrice)
            ->setPostagePrice($postagePrice)
            ->setTotalWeight($totalWeight)
            ->setIsCancel($isCancel)
            ->setShipping($Shipping);


        $this->assertEquals([
            $shippingDateSchedule,
            $arrivalDateSchedule,
            $customerName,
            $customerZip,
            $customerAddress,
            $customerTel,
            $totalPrice,
            $discountedPrice,
            $taxPrice,
            $postagePrice,
            $totalWeight,
            $isCancel,
            $Shipping
        ],
            [
                $shippingScheduleHeader->getShippingDateSchedule(),
                $shippingScheduleHeader->getArrivalDateSchedule(),
                $shippingScheduleHeader->getCustomerName(),
                $shippingScheduleHeader->getCustomerZip(),
                $shippingScheduleHeader->getCustomerAddress(),
                $shippingScheduleHeader->getCustomerTel(),
                $shippingScheduleHeader->getTotalPrice(),
                $shippingScheduleHeader->getDiscountedPrice(),
                $shippingScheduleHeader->getTaxPrice(),
                $shippingScheduleHeader->getPostagePrice(),
                $shippingScheduleHeader->getTotalWeight(),
                $shippingScheduleHeader->getIsCancel(),
                $shippingScheduleHeader->getShipping(),
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
        $shippingScheduleHeader = new ShippingScheduleHeader();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $shippingScheduleHeader->getShippingSchedule());

        // Conservation contact header can have one
        $this->assertNull($shippingScheduleHeader->getShipping());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $shippingScheduleHeader = new ShippingScheduleHeader();

        $shippingScheduleHeader->setShippingDateSchedule(new DateTime())
            ->setArrivalDateSchedule(new DateTime())
            ->setShippingDate(new DateTime())
            ->setWmsSendDate(new DateTime())
            ->setWmsReciveDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $shippingScheduleHeader->getShippingDateSchedule());
        $this->assertInstanceOf(DateTime::class, $shippingScheduleHeader->getArrivalDateSchedule());
        $this->assertInstanceOf(DateTime::class, $shippingScheduleHeader->getShippingDate());
        $this->assertInstanceOf(DateTime::class, $shippingScheduleHeader->getWmsSendDate());
        $this->assertInstanceOf(DateTime::class, $shippingScheduleHeader->getWmsReciveDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $shippingScheduleHeader = new ShippingScheduleHeader();

        $integerVal = 1;
        $shippingScheduleHeader->setTotalPrice($integerVal)
            ->setDiscountedPrice($integerVal)
            ->setTaxPrice($integerVal)
            ->setPostagePrice($integerVal)
            ->setTotalWeight($integerVal)
            ->setShippingUnits($integerVal)
            ->setIsCancel($integerVal)
            ->setCancelReason($integerVal);

        $this->assertEquals($integerVal, $shippingScheduleHeader->getTotalPrice());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getDiscountedPrice());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getTaxPrice());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getPostagePrice());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getTotalWeight());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getShippingUnits());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getIsCancel());
        $this->assertEquals($integerVal, $shippingScheduleHeader->getCancelReason());
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValues(): void
    {
        $shippingScheduleHeader = new ShippingScheduleHeader();

        // below fields must have a default value
        $this->assertEquals(0, $shippingScheduleHeader->getIsCancel());
    }
}
