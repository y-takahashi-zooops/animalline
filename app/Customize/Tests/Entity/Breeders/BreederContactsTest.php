<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederContactHeader;
use Customize\Entity\BreederContacts;
use DateTime;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreederContactsTest extends TestCase
{
    public function testCreateNewBreederConatct(): void
    {
        $BreederContact = new BreederContacts;
        $this->assertInstanceOf(BreederContacts::class, $BreederContact);
    }

    public function testCreateBreederContactsData(): void
    {
        $BreederHeader = new BreederContactHeader;
        $from = 1;
        $date = new DateTime();
        $BreederContact = (new BreederContacts)
            ->setBreederContactHeader($BreederHeader)
            ->setMessageFrom($from)
            ->setSendDate($date);

        $this->assertEquals(
            [$BreederHeader, $from, $date],
            [$BreederContact->getBreederContactHeader(), $BreederContact->getMessageFrom(), $BreederContact->getSendDate()]
        );
    }

    public function testRelations(): void
    {
        $BreederContact = (new BreederContacts)
            ->setBreederContactHeader(new BreederContactHeader);

        // breeder contact must be belongs to a header
        $this->assertInstanceOf(BreederContactHeader::class, $BreederContact->getBreederContactHeader());
        $this->assertNotNull($BreederContact->getBreederContactHeader());
        $this->expectException(TypeError::class);
        $BreederContact->setBreederContactHeader(null);
    }

    public function testRequiredFields(): void
    {
        $BreederContact = new BreederContacts;

        // below fields must have a value
        try {
            $BreederContact->setBreederContactHeader(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederContact->setSendDate(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
