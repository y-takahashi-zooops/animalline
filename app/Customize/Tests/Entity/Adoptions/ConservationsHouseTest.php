<?php

namespace Customize\Tests\Entity\Adoptions;

use Customize\Entity\Conservations;
use Customize\Entity\ConservationsHouse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ConservationsHouseTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewConservationHouse(): void
    {
        $conservationHouse = new ConservationsHouse();

        $this->assertInstanceOf(ConservationsHouse::class, $conservationHouse);
    }

    public function testCreateConservationData(): void
    {
        $ConservationHouse = new ConservationsHouse();
        $petType = 1;
        $ConservationHouse->setPetType($petType);

        $this->assertEquals($petType, $ConservationHouse->getPetType());
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testRelation(): void
    {
        $ConservationHouse = new ConservationsHouse();

        // ConservationHouse can have one
        $this->assertNull($ConservationHouse->getConservation());
        $this->assertNull($ConservationHouse->getConservationHousePref());
    }

    /**
     * Test length is true
     *
     * @return void
     */
    public function testLengthTrue(): void
    {
        $ConservationHouse = new ConservationsHouse();

        $conservation_house_pref = '12345';
        $conservation_house_city = '青森県';
        $conservation_house_front_tel = '4687343675';

        $ConservationHouse->setConservationHousePref($conservation_house_pref)
                    ->setConservationHouseCity($conservation_house_city)
                    ->setConservationHouseFrontTel($conservation_house_front_tel);

        $this->assertEquals(
            [
                $conservation_house_pref,
                $conservation_house_city,
                $conservation_house_front_tel
            ], 
            [
                $ConservationHouse->getConservationHousePref(),
                $ConservationHouse->getConservationHouseCity(),
                $ConservationHouse->getConservationHouseFrontTel()
            ]
        );
    }

    /**
     * Test length is false
     *
     * @return void
     */
    public function testLengthFalse(): void
    {
        $ConservationHouse = new ConservationsHouse();

        $conservation_house_pref = '1234546546';
        $conservation_house_city = '青森県青森県青森県';
        $conservation_house_front_tel = '46873436755343';

        $ConservationHouse->setConservationHousePref($conservation_house_pref)
                    ->setConservationHouseCity($conservation_house_city)
                    ->setConservationHouseFrontTel($conservation_house_front_tel);

        $foo = false;
        $this->assertFalse($foo);
    }

    /**
     * Test validate pass
     *
     * @return void
     */
    public function testValidatePass(): void
    {
        $ConservationHouse = new ConservationsHouse();
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $errors = $validator->validate($ConservationHouse);
        $this->assertEquals(0, count($errors));
    }
}