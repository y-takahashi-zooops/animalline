<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederHouse;
use Customize\Entity\Breeders;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Eccube\Entity\Master\Pref;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

class BreedersTest extends TestCase
{
    public function testCreateNewBreeder(): void
    {
        $Breeder = new Breeders;
        $this->assertInstanceOf(Breeders::class, $Breeder);
    }

    public function testCreateBreederData(): void
    {
        $breederName = 'breeder name';
        $breederKana = 'お名前(カナ)';
        $Breeder = (new Breeders)
            ->setBreederName($breederName)
            ->setBreederKana($breederKana);

        $this->assertEquals($breederName, $Breeder->getBreederName());
        $this->assertEquals($breederKana, $Breeder->getBreederKana());
    }

    public function testRelations(): void
    {
        $Breeder = new Breeders;

        // breeder must have many
        $this->assertInstanceOf(Collection::class, $Breeder->getBreederPets());
        $this->assertInstanceOf(Collection::class, $Breeder->getBreederContactHeader());
        $this->assertInstanceOf(Collection::class, $Breeder->getBreederExaminationInfos());
        $this->assertInstanceOf(Collection::class, $Breeder->getBreederHouses());

        // breeder can have one
        $this->assertNull($Breeder->getPrefBreeder());
        $this->assertNull($Breeder->getPrefLicense());
    }

    public function testDefaulValues(): void
    {
        $Breeder = new Breeders;

        // below fields must have a default value
        $this->assertEquals(0, $Breeder->getExaminationStatus());
        $this->assertEquals(0, $Breeder->getHandlingPetKind());
    }

    public function testDefaultActive(): void
    {
        $Breeder = new Breeders;

        // breeder must be not active by default
        $this->assertEquals(0, $Breeder->getIsActive());
    }

    public function testBreederRank(): void
    {
        // breeder rank must have default value
        $Breeder = new Breeders;
        $this->assertEquals(0, $Breeder->getBreederRank());

        // breeder rank must accept decimal value
        $decimalVal = 1.23;
        $Breeder->setBreederRank($decimalVal);
        $this->assertEquals($decimalVal, $Breeder->getBreederRank());

        // breeder rank must have a value
        $this->expectException(TypeError::class);
        $Breeder->setBreederRank(null);
    }

    public function testLicenseRegistDate(): void
    {
        $Breeder = (new Breeders)
            ->setLicenseRegistDate(new DateTime())
            ->setLicenseExpireDate(new DateTime());

        // license regist date must be a valid date
        $this->assertInstanceOf(DateTime::class, $Breeder->getLicenseRegistDate());
        // license expire date must be a valid date
        $this->assertInstanceOf(DateTime::class, $Breeder->getLicenseExpireDate());
    }

    public function testGetBreederHouseByPetType(): void
    {
        $Breeder = new Breeders;

        // breeder can have houses for pets
        $this->assertInstanceOf(BreederHouse::class, $Breeder->getBreederHouseByPetType(1));
    }

    public function testLicenseType(): void
    {
        $Breeder = new Breeders;

        // breeder can have license type
        $this->assertNull($Breeder->getLicenseType());
        // license type must be integer value
        $Breeder->setLicenseType(1);
        $this->assertInternalType("int", $Breeder->getLicenseType());
    }
}
