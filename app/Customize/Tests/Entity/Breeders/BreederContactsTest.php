<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederContactHeader;
use Customize\Entity\BreederContacts;
use Customize\Entity\BreederEvaluations;
use Customize\Entity\BreederPets;
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
            ->setBreederHeader($BreederHeader)
            ->setMessageFrom($from)
            ->setSendDate($date);

        $this->assertEquals(
            [$BreederHeader, $from, $date],
            [$BreederContact->getBreederHeader(), $BreederContact->getMessageFrom(), $BreederContact->getSendDate()]
        );
    }

    public function testRelations(): void
    {
        $BreederContact = (new BreederContacts)
            ->setBreederHeader(new BreederContactHeader);

        // breeder contact must be belongs to a header
        $this->assertInstanceOf(BreederContactHeader::class, $BreederContact->getBreederHeader());
        $this->assertNotNull($BreederContact->getBreederHeader());
        $this->expectException(TypeError::class);
        $BreederContact->setBreederHeader(null);
    }

    public function testRequiredFields(): void
    {
        $BreederContact = new BreederContacts;

        // below fields must have a value
        try {
            $BreederContact->setBreederHeader(null);
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
