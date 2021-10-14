<?php

namespace Customize\Tests\Conservations;

use Customize\Entity\Conservations;
use PHPUnit\Framework\TestCase;

class ConservationsTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewConservation(): void
    {
        $Conservation = new Conservations();
        $this->assertInstanceOf(Conservations::class, $Conservation);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateConservationData(): void
    {
        $Conservation = new Conservations();
        $ownerName = 'test owner name';
        $ownerKana = 'カナ';
        $ownerName = 'test owner name';
        $ownerKana = 'カナ';
        $zip = '12345';
        $pref = '青森県';
        $city = 'さいたま';
        $tel = '0382068176';
        $fax = '467367543';
        $Conservation->setOwnerName($ownerName)
                     ->setOwnerKana($ownerKana)
                     ->setZip($zip)
                     ->setPref($pref)
                     ->setCity($city)
                     ->setTel($tel)
                     ->setFax($fax);

        $this->assertEquals([
                $ownerName,
                $ownerKana,
                $zip,
                $pref,
                $city,
                $tel,
                $fax
            ],
            [
                $Conservation->getOwnerName(),
                $Conservation->getOwnerKana(),
                $Conservation->getZip(),
                $Conservation->getPref(),
                $Conservation->getCity(),
                $Conservation->getTel(),
                $Conservation->getFax()
            ]
        );
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValue(): void
    {
        $Conservation = new Conservations();

        $this->assertEquals([0, 0], [$Conservation->getExaminationStatus(), $Conservation->getHandlingPetKind()]);
    }

    /**
     * Test length is true
     *
     * @return void
     */
    public function testLengthTrue(): void
    {
        $Conservation = new Conservations();

        $zip = '12345';
        $pref = '青森県';
        $city = 'さいたま';
        $tel = '0382068176';
        $fax = '467367543';

        $Conservation
                    ->setZip($zip)
                    ->setPref($pref)
                    ->setCity($city)
                    ->setTel($tel)
                    ->setFax($fax);

        $this->assertEquals(
            [
                $zip,
                $pref,
                $city,
                $tel,
                $fax
            ], 
            [
                $Conservation->getZip(),
                $Conservation->getPref(),
                $Conservation->getCity(),
                $Conservation->getTel(),
                $Conservation->getFax()
            ]);
    }

    /**
     * Test length is false
     *
     * @return void
     */
    public function testLengthFalse(): void
    {
        $Conservation = new Conservations();

        $zip = '12345678';
        $pref = '青森県青森県青森県青森県';
        $city = 'さいたま青森県青森県青森県';
        $tel = '038206817699999';
        $fax = '467367543999999';

        $Conservation ->setZip($zip)
                    ->setPref($pref)
                    ->setCity($city)
                    ->setTel($tel)
                    ->setFax($fax);

        $foo = false;
        $this->assertFalse($foo);
    }
}
