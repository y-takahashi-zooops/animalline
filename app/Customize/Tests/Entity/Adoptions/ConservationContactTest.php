<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationContacts;
use DateTime;
use PHPUnit\Framework\TestCase;

class ConservationContactTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewConservationContact(): void
    {
        $ConservationContact = new ConservationContacts();
        $this->assertInstanceOf(ConservationContacts::class, $ConservationContact);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateConservationContactData(): void
    {
        $ConservationContact = new ConservationContacts();
        $messageFrom = 1;
        $contactDescription = 'abc123';
        $conservationContactHeader = new ConservationContactHeader;
        $ConservationContact->setMessageFrom($messageFrom)
                            ->setContactDescription($contactDescription)
                            ->setConservationHeader($conservationContactHeader);


        $this->assertEquals([
                $messageFrom,
                $contactDescription,
                $conservationContactHeader
            ],
            [
                $ConservationContact->getMessageFrom(),
                $ConservationContact->getContactDescription(),
                $ConservationContact->getConservationHeader()
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
        $ConservationContact = new ConservationContacts();

        // conservation can have one
        $this->assertNull($ConservationContact->getConservationHeader());
    }

    /**
     * Test datetime
     *
     * @return void
     */
    public function testDate(): void
    {
        $ConservationContact = new ConservationContacts();
        $ConservationContact->setSendDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $ConservationContact->getSendDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $ConservationContact = new ConservationContacts();
        $integerVal = 1;
        $ConservationContact->setMessageFrom($integerVal);

        $this->assertEquals($integerVal, $ConservationContact->getMessageFrom());
    }
}
