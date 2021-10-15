<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\ConservationPets;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ConservationPetsTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewConservationPets(): void
    {
        $ConservationsPet = new ConservationPets();
        $this->assertInstanceOf(ConservationPets::class, $ConservationsPet);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateConservationData(): void
    {
        $ConservationsPet = new ConservationPets();
        $petKind = 1;
        $petSex = 1;
        $petBirthday = new DateTime();
        $ConservationsPet->setPetKind($petKind)
                     ->setPetSex($petSex)
                     ->setPetBirthday($petBirthday);

        $this->assertEquals([
                $petKind,
                $petSex,
                $petBirthday
            ],
            [
                $ConservationsPet->getPetKind(),
                $ConservationsPet->getPetSex(),
                $ConservationsPet->getPetBirthday()
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
        $ConservationsPet = new ConservationPets();

        // conservation must have many
        $this->assertInstanceOf(Collection::class, $ConservationsPet->getConservationPetImages());
        $this->assertInstanceOf(Collection::class, $ConservationsPet->getPetsFavorites());
        $this->assertInstanceOf(Collection::class, $ConservationsPet->getConservationHeader());

        // conservation can have one
        $this->assertNull($ConservationsPet->getConservation());
        $this->assertNull($ConservationsPet->getCoatColor());
        $this->assertNull($ConservationsPet->getBreedsType());
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValue(): void
    {
        $ConservationsPet = new ConservationPets();

        $this->assertEquals([0, 0], [$ConservationsPet->getReleaseStatus(), $ConservationsPet->getFavoriteCount()]);
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDate(): void
    {
        $ConservationsPet = new ConservationPets();
        $ConservationsPet->setPetBirthday(new DateTime())
                         ->setReleaseDate(new DateTime());

        // birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $ConservationsPet->getPetBirthday());
        // release status must be a valid date
        $this->assertInstanceOf(DateTime::class, $ConservationsPet->getReleaseDate());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $ConservationsPet = new ConservationPets();
        $integerVal = 1;
        $ConservationsPet->setDnaCheckResult($integerVal)
                         ->setPrice($integerVal)
                         ->setFavoriteCount($integerVal);

        $this->assertEquals($integerVal, $ConservationsPet->getDnaCheckResult());
        $this->assertEquals($integerVal, $ConservationsPet->getPrice());
        $this->assertEquals($integerVal, $ConservationsPet->getFavoriteCount());
    }
}
