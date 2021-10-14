<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\Breeders;
use PHPUnit\Framework\TestCase;

class BreedersTest extends TestCase
{
    public function testCreateNewBreeder(): void
    {
        $Breeder = new Breeders();
        $this->assertInstanceOf(Breeders::class, $Breeder);
    }

    public function testCreateBreederData(): void
    {
        $Breeder = new Breeders();
        $testName = 'test breeder name';
        $Breeder->setBreederName($testName);

        $this->assertEquals($testName, $Breeder->getBreederName());
    }

    public function testBreederDefaultRank(): void
    {
        $Breeder = new Breeders();

        $this->assertEquals(0, $Breeder->getBreederRank());
    }

    public function testDefaultActive(): void
    {
        $Breeder = new Breeders();

        $this->assertEquals(0, $Breeder->getIsActive());
    }

    public function testDefaulValue(): void
    {
        $Breeder = new Breeders();

        $this->assertEquals(0, $Breeder->getExaminationStatus());
        $this->assertEquals(0, $Breeder->getHandlingPetKind());
    }
}
