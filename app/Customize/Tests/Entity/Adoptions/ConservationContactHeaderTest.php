<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationPets;
use Customize\Entity\Conservations;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Eccube\Entity\Customer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ConservationContactHeaderTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewConservationContactHeader(): void
    {
        $ConservationContactHeader = new ConservationContactHeader();
        $this->assertInstanceOf(ConservationContactHeader::class, $ConservationContactHeader);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateConservationContactData(): void
    {
        $ConservationContactHeader = new ConservationContactHeader();
        $customer = new Customer;
        $conservation = new Conservations;
        $pet = new ConservationPets;
        $contactType = 1;
        $sendDate = new DateTime();

        $ConservationContactHeader->setCustomer($customer)
                            ->setConservation($conservation)
                            ->setPet($pet)
                            ->setContactType($contactType)
                            ->setSendDate($sendDate);


        $this->assertEquals([
                $customer,
                $conservation,
                $pet,
                $contactType,
                $sendDate
            ],
            [
                $ConservationContactHeader->getCustomer(),
                $ConservationContactHeader->getConservation(),
                $ConservationContactHeader->getPet(),
                $ConservationContactHeader->getContactType(),
                $ConservationContactHeader->getSendDate()
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
        $ConservationContactHeader = new ConservationContactHeader();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $ConservationContactHeader->getConservationContacts());

        // Conservation contact header can have one
        $this->assertNull($ConservationContactHeader->getConservation());
        $this->assertNull($ConservationContactHeader->getCustomer());
        $this->assertNull($ConservationContactHeader->getPet());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $ConservationContactHeader = new ConservationContactHeader();
        $ConservationContactHeader->setLastMessageDate(new DateTime())
                                ->setSendDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $ConservationContactHeader->getSendDate());
        $this->assertInstanceOf(DateTime::class, $ConservationContactHeader->getLastMessageDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $ConservationContactHeader = new ConservationContactHeader();
        $integerVal = 1;
        $ConservationContactHeader->setContactType($integerVal)
                                ->setContractStatus($integerVal)
                                ->setSendoffReason($integerVal)
                                ->setCustomerCheck($integerVal)
                                ->setConservationCheck($integerVal)
                                ->setConservationNewMsg($integerVal)
                                ->setCustomerNewMsg($integerVal);

        $this->assertEquals($integerVal, $ConservationContactHeader->getContactType());
        $this->assertEquals($integerVal, $ConservationContactHeader->getContractStatus());
        $this->assertEquals($integerVal, $ConservationContactHeader->getSendoffReason());
        $this->assertEquals($integerVal, $ConservationContactHeader->getCustomerCheck());
        $this->assertEquals($integerVal, $ConservationContactHeader->getConservationCheck());
        $this->assertEquals($integerVal, $ConservationContactHeader->getConservationNewMsg());
        $this->assertEquals($integerVal, $ConservationContactHeader->getCustomerNewMsg());
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValues(): void
    {
        $ConservationContactHeader = new ConservationContactHeader();

        // below fields must have a default value
        $this->assertEquals(0, $ConservationContactHeader->getContractStatus());
        $this->assertEquals(0, $ConservationContactHeader->getSendoffReason());
        $this->assertEquals(0, $ConservationContactHeader->getCustomerCheck());
        $this->assertEquals(0, $ConservationContactHeader->getConservationCheck());
        $this->assertEquals(1, $ConservationContactHeader->getConservationNewMsg());
        $this->assertEquals(0, $ConservationContactHeader->getCustomerNewMsg());
    }

    /**
     * Test validate pass
     *
     * @return void
     */
    public function testValidatePass(): void
    {
        $ConservationContactHeader = new ConservationContactHeader();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $errors = $validator->validate($ConservationContactHeader);
        $this->assertEquals(0, count($errors));
    }
}
