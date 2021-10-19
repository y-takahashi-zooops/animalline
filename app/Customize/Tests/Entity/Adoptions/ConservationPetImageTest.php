<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\ConservationPetImage;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ConservationPetImageTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewConservationPets(): void
    {
        $ConservationPetImage = new ConservationPetImage();
        $this->assertInstanceOf(ConservationPetImage::class, $ConservationPetImage);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateConservationData(): void
    {
        $ConservationPetImage = new ConservationPetImage();
        $ConservationPetImage->setImageType(1);

        $this->assertEquals(1, $ConservationPetImage->getImageType());
    }

    /**
     * Test relation
     * 
     * @return void
     */
    public function testRelations(): void
    {
        $ConservationPetImage = new ConservationPetImage();

        // conservation can have one
        $this->assertNull($ConservationPetImage->getConservationPet());
    }
}
