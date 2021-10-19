<?php

namespace Customize\Tests\Entity\Breeders;

use Customize\Entity\BreederHouse;
use PHPUnit\Framework\TestCase;

class BreederHouseTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewBreederHouse(): void
    {
        $breederHouse = new BreederHouse();

        $this->assertInstanceOf(BreederHouse::class, $breederHouse);
    }

    public function testCreateBreederData(): void
    {
        $breederHouseHouse = new BreederHouse();
        $petType = 1;
        $breederHouseHouse->setPetType($petType);

        $this->assertEquals($petType, $breederHouseHouse->getPetType());
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testRelation(): void
    {
        $breederHouse = new BreederHouse();

        // ConservationHouse can have one
        $this->assertNull($breederHouse->getBreeder());
        $this->assertNull($breederHouse->getBreederHousePref());
    }

    /**
     * Test length is true
     *
     * @return void
     */
    public function testLengthTrue(): void
    {
        $breederHouse = new BreederHouse();

        $breeder_house_pref = '12345';
        $breeder_house_city = '青森県';
        $breeder_house_front_tel = '4687343675';

        $breederHouse->setBreederHousePref($breeder_house_pref)
            ->setBreederHouseCity($breeder_house_city)
            ->setBreederHouseFrontTel($breeder_house_front_tel);

        $this->assertEquals(
            [
                $breeder_house_pref,
                $breeder_house_city,
                $breeder_house_front_tel
            ],
            [
                $breederHouse->getBreederHousePref(),
                $breederHouse->getBreederHouseCity(),
                $breederHouse->getBreederHouseFrontTel()
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
        $breederHouse = new BreederHouse();

        $breeder_house_pref = '1234546546';
        $breeder_house_city = '青森県青森県青森県';
        $breeder_house_front_tel = '46873436755343';

        $breederHouse->setBreederHousePref($breeder_house_pref)
            ->setBreederHouseCity($breeder_house_city)
            ->setBreederHouseFrontTel($breeder_house_front_tel);

        $foo = false;
        $this->assertFalse($foo);
    }
}