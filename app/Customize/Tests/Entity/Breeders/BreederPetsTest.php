<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederPets;
use Customize\Entity\Breeders;
use Customize\Entity\Breeds;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class BreederPetsTest extends TestCase
{
    public function testCreateNewBreeder(): void
    {
        $BreederPet = new BreederPets;
        $this->assertInstanceOf(BreederPets::class, $BreederPet);
    }

    public function testCreateBreederPetsData(): void
    {
        $breederName = 'breeder name';
        $breederKana = 'お名前(カナ)';
        $Breeder = (new Breeders)
            ->setBreederName($breederName)
            ->setBreederKana($breederKana);
        $Breeds = new Breeds;
        $coatColor = 'coat color';

        $BreederPets = (new BreederPets)
            ->setPetKind(1)
            ->setPetSex(1)
            ->setBreeder($Breeder)
            ->setBreedsType($Breeds)
            ->setCoatColor($coatColor);

        $this->assertEquals(1, $BreederPets->getPetKind());
        $this->assertEquals(1, $BreederPets->getPetSex());
        $this->assertEquals($Breeder, $BreederPets->getBreeder());
        $this->assertEquals($Breeds, $BreederPets->getBreedsType());
        $this->assertEquals($coatColor, $BreederPets->getCoatColor());
    }

    public function testRelations(): void
    {
        $BreederPets = new BreederPets;

        // breeder pet have many
        $this->assertInstanceOf(Collection::class, $BreederPets->getBreederPetImages());
        $this->assertInstanceOf(Collection::class, $BreederPets->getPetsFavorites());
        $this->assertInstanceOf(Collection::class, $BreederPets->getBreederContactHeader());
        $this->assertInstanceOf(Collection::class, $BreederPets->getBreederEvaluations());

        // breeder pet have one
        $this->assertNull($BreederPets->getBreedsType());
        $this->assertNull($BreederPets->getCoatColor());
    }

    public function testDefaulValues(): void
    {
        $BreederPet = new BreederPets;

        // below fields must have a default value
        $this->assertEquals(0, $BreederPet->getFavoriteCount());
        $this->assertEquals(0, $BreederPet->getDnaCheckResult());
    }

    public function testPetBirthday(): void
    {
        $BreederPet = (new BreederPets)
            ->setPetBirthday(new DateTime());

        // pet birthday must be a valid date
        $this->assertInstanceOf(DateTime::class, $BreederPet->getPetBirthday());
    }
}
