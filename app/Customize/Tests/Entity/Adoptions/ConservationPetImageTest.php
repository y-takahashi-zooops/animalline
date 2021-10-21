<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\ConservationPetImage;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

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

    /**
     * Test validate pass
     *
     * @return void
     */
    public function testValidatePass(): void
    {
        $ConservationPetImage = new ConservationPetImage();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $errors = $validator->validate($ConservationPetImage);
        $this->assertEquals(0, count($errors));
    }
}
