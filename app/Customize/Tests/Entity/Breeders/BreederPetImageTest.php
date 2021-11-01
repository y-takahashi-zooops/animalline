<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\BreederPetImage;
use Customize\Entity\BreederPets;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreederPetImageTest extends TestCase
{
    public function testCreateNewBreederPetImage(): void
    {
        $BreederPetImage = new BreederPetImage;
        $this->assertInstanceOf(BreederPetImage::class, $BreederPetImage);
    }

    public function testCreateBreederPetImageData(): void
    {
        $type = 1;
        $path = 'path/to/image';
        $BreederPetImage = (new BreederPetImage)
            ->setImageType($type)
            ->setImageUri($path);
        $this->assertEquals(
            [$type, $path],
            [$BreederPetImage->getImageType(), $BreederPetImage->getImageUri()]
        );
    }

    public function testRelations(): void
    {
        $BreederPetImage = (new BreederPetImage)
            ->setBreederPet(new BreederPets);

        // breeder pet image must be belong to a breeder pet
        $this->assertNotNull($BreederPetImage->getBreederPet());
        $this->assertInstanceOf(BreederPets::class, $BreederPetImage->getBreederPet());
        $this->expectException(TypeError::class);
        $BreederPetImage->setBreederPet(null);
    }

    public function testRequiredFields()
    {
        $BreederPetImage = new BreederPetImage;
        try {
            $BreederPetImage->setBreederPet(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederPetImage->setImageType(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederPetImage->setImageUri(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederPetImage->setSortOrder(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
