<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederExaminationInfo;
use Customize\Entity\Breeders;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreederExaminationInfoTest extends TestCase
{
    public function testCreateNewBreeder(): void
    {
        $BreederExaminationInfo = new BreederExaminationInfo;
        $this->assertInstanceOf(BreederExaminationInfo::class, $BreederExaminationInfo);
    }

    public function testCreateBreederExaminationInfoData(): void
    {
        $breederName = 'breeder name';
        $breederKana = 'お名前(カナ)';
        $Breeder = (new Breeders)
            ->setBreederName($breederName)
            ->setBreederKana($breederKana);
        $BreederExaminationInfo = (new BreederExaminationInfo)
            ->setBreeder($Breeder)
            ->setPetType(1);

        $this->assertEquals($Breeder, $BreederExaminationInfo->getBreeder());
        $this->assertEquals(1, $BreederExaminationInfo->getPetType());
    }

    public function testRelations(): void
    {
        $Breeder = new Breeders;
        $BreederExaminationInfo = (new BreederExaminationInfo)
            ->setBreeder($Breeder);

        // breeder examination info belongs to breeder
        $this->assertInstanceOf(Breeders::class, $BreederExaminationInfo->getBreeder());

        // breeder examination info must have a breeder
        $this->assertNotNull($BreederExaminationInfo->getBreeder());
        $this->expectException(TypeError::class);
        $BreederExaminationInfo->setBreeder(null);
    }

    public function testDefaulValues(): void
    {
        $BreederExaminationInfo = new BreederExaminationInfo;

        // below fields must have a default value
        $this->assertEquals(0, $BreederExaminationInfo->getCageSize1());
        $this->assertEquals(0, $BreederExaminationInfo->getCageSize2());
        $this->assertEquals(0, $BreederExaminationInfo->getCageSize3());
        $this->assertEquals(0, $BreederExaminationInfo->getInputStatus());
        $this->assertEquals(0, $BreederExaminationInfo->getExaminationResult());
    }

    public function testRequiredFields(): void
    {
        $BreederExaminationInfo = new BreederExaminationInfo;

        // below fields must have a value
        $BreederExaminationInfo->setBreeder(new Breeders);
        $BreederExaminationInfo->setPetType(1);
        $this->assertNotNull($BreederExaminationInfo->getBreeder());
        $this->assertNotNull($BreederExaminationInfo->getPetType());
        $this->assertNotNull($BreederExaminationInfo->getCageSize1());
        $this->assertNotNull($BreederExaminationInfo->getCageSize2());
        $this->assertNotNull($BreederExaminationInfo->getCageSize3());
        $this->assertNotNull($BreederExaminationInfo->getInputStatus());
        $this->assertNotNull($BreederExaminationInfo->getExaminationResult());
    }
}
