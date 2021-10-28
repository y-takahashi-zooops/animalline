<?php

namespace Customize\Tests\Entity;

use Customize\Entity\SendoffReasons;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use TypeError;

class SendoffReasonsTest extends TestCase
{
    public function testCreateNewSendoffReasons(): void
    {
        $SendoffReason = new SendoffReasons;
        $this->assertInstanceOf(SendoffReasons::class, $SendoffReason);
    }

    public function testCreateSendoffReasonsData(): void
    {
        $reason = 'reason';
        $isAdoptionVisible = 1;
        $isBreederVisible = 1;
        $SendoffReason = (new SendoffReasons)
            ->setReason($reason)
            ->setIsAdoptionVisible($isAdoptionVisible)
            ->setIsBreederVisible($isBreederVisible);

        $this->assertEquals(
            [$reason, $isAdoptionVisible, $isBreederVisible],
            [$SendoffReason->getReason(), $SendoffReason->getIsAdoptionVisible(), $SendoffReason->getIsBreederVisible()]
        );
    }

    public function testRelations(): void
    {
        $SendoffReason = new SendoffReasons;

        // send off reason can be used by many breeder pets, conservation pets
        $this->assertInstanceOf(Collection::class, $SendoffReason->getConservationContacts());
        $this->assertInstanceOf(Collection::class, $SendoffReason->getBreederContacts());
        $this->assertNotNull($SendoffReason->getConservationContacts());
        $this->assertNotNull($SendoffReason->getBreederContacts());
    }

    public function testRequiredFields(): void
    {
        $SendoffReason = new SendoffReasons;

        // below fields must have a value
        try {
            $SendoffReason->setReason(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $SendoffReason->setIsAdoptionVisible(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $SendoffReason->setIsBreederVisible(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }

    public function testDefaulValues(): void
    {
        $SendoffReason = new SendoffReasons;

        // below fields must have a default value
        $this->assertEquals(0, $SendoffReason->getIsAdoptionVisible());
        $this->assertEquals(0, $SendoffReason->getIsBreederVisible());
    }
}
