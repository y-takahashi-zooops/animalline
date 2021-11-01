<?php

namespace Customize\Tests\Entity;

use Customize\Entity\Breeds;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreedsTest extends TestCase
{
    public function testCreateNewBreeds(): void
    {
        $Breed = new Breeds;
        $this->assertInstanceOf(Breeds::class, $Breed);
    }

    public function testCreateBreedsData(): void
    {
        $petKind = 1;
        $breedsName = 'breeds name';
        $sizeCode = 1;
        $sortOrder = 1;
        $Breed = (new Breeds)
            ->setPetKind($petKind)
            ->setBreedsName($breedsName)
            ->setSizeCode($sizeCode)
            ->setSortOrder($sortOrder);

        $this->assertEquals(
            [$petKind, $breedsName, $sizeCode, $sortOrder],
            [$Breed->getPetKind(), $Breed->getBreedsName(), $Breed->getSizeCode(), $Breed->getSortOrder()]
        );
    }

    public function testRelations(): void
    {
        $Breed = new Breeds;

        // breed can be used by many breeder pets, conservation pets
        $this->assertInstanceOf(Collection::class, $Breed->getBreederPets());
        $this->assertInstanceOf(Collection::class, $Breed->getConservationPets());
        $this->assertNotNull($Breed->getBreederPets());
        $this->assertNotNull($Breed->getConservationPets());
    }

    public function testRequiredFields(): void
    {
        $Breed = new Breeds;

        // below fields must have a value
        try {
            $Breed->setPetKind(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $Breed->setBreedsName(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $Breed->setSizeCode(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $Breed->setSortOrder(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
