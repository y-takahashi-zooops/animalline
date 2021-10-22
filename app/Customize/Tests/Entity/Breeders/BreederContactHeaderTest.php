<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederContactHeader;
use Customize\Entity\BreederEvaluations;
use Customize\Entity\BreederPets;
use Customize\Entity\Breeders;
use Eccube\Entity\Customer;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreederContactHeaderTest extends TestCase
{
    public function testCreateNewBreederContactHeader(): void
    {
        $BreederContactHeader = new BreederContactHeader;
        $this->assertInstanceOf(BreederContactHeader::class, $BreederContactHeader);
    }

    public function testCreateBreederContactHeaderData(): void
    {
        $Custmer = new Customer;
        $Breeder = new Breeders;
        $Pet = new BreederPets;
        $BreederContactHeader = (new BreederContactHeader)
            ->setCustomer($Custmer)
            ->setBreeder($Breeder)
            ->setPet($Pet);

        $this->assertEquals(
            [$Custmer, $Breeder, $Pet],
            [
                $BreederContactHeader->getCustomer(),
                $BreederContactHeader->getBreeder(),
                $BreederContactHeader->getPet()
            ]
        );
    }

    public function testRelations(): void
    {
        $BreederContactHeader =  (new BreederContactHeader)
            ->setCustomer(new Customer)
            ->setBreeder(new Breeders)
            ->setPet(new BreederPets);

        // breeder contact header must be belong to a customer
        $this->assertInstanceOf(Customer::class, $BreederContactHeader->getCustomer());
        $this->assertNotNull($BreederContactHeader->getCustomer());
        // breeder contact header must be belong to a breeder
        $this->assertInstanceOf(Breeders::class, $BreederContactHeader->getBreeder());
        $this->assertNotNull($BreederContactHeader->getBreeder());
        // breeder contact header must be belong to a pet
        $this->assertInstanceOf(BreederPets::class, $BreederContactHeader->getPet());
        $this->assertNotNull($BreederContactHeader->getPet());
        // breeder contact header have contacts
        $this->assertInstanceOf(Collection::class, $BreederContactHeader->getBreederContacts());
        $this->assertNotNull($BreederContactHeader->getBreederContacts());
    }

    public function testRequiredFields(): void
    {
        $BreederContactHeader = new BreederContactHeader;

        // below fields must have a value
        try {
            $BreederContactHeader->setCustomer(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setBreeder(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setPet(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setContactType(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setSendDate(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setContractStatus(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setCustomerCheck(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setBreederCheck(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setCustomerNewMsg(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContactHeader->setBreederNewMsg(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }

    public function testDefaulValues(): void
    {
        $BreederContactHeader = new BreederContactHeader;

        // below fields must have a default value
        $this->assertEquals(0, $BreederContactHeader->getContractStatus());
        $this->assertEquals(0, $BreederContactHeader->getSendoffReason());
        $this->assertEquals(0, $BreederContactHeader->getCustomerCheck());
        $this->assertEquals(0, $BreederContactHeader->getBreederCheck());
        $this->assertEquals(1, $BreederContactHeader->getBreederNewMsg());
        $this->assertEquals(0, $BreederContactHeader->getCustomerNewMsg());
    }
}
