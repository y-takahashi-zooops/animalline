<?php

namespace Customize\Tests\Entity\Breeders;

use Customize\Entity\BreederPetinfoTemplate;
use Customize\Entity\Breeders;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreederPetinfoTemplateTest extends TestCase
{
    public function testCreateNewBreederPetinfoTemplate(): void
    {
        $BreederPetinfoTemplate = new BreederPetinfoTemplate;
        $this->assertInstanceOf(BreederPetinfoTemplate::class, $BreederPetinfoTemplate);
    }

    public function testCreateBreederPetinfoTemplateData(): void
    {
        $Breeder = new Breeders;
        $deliveryWay = 'delivery way';
        $paymentMethod = 'payment method';
        $BreederPetinfoTemplate = (new BreederPetinfoTemplate)
            ->setBreeder($Breeder)
            ->setDeliveryWay($deliveryWay)
            ->setPaymentMethod($paymentMethod);

        $this->assertEquals(
            [$Breeder, $deliveryWay, $paymentMethod],
            [$BreederPetinfoTemplate->getBreeder(), $BreederPetinfoTemplate->getDeliveryWay(), $BreederPetinfoTemplate->getPaymentMethod()]
        );
    }

    public function testRelations(): void
    {
        $Breeder = new Breeders;
        $BreederPetinfoTemplate = (new BreederPetinfoTemplate)
            ->setBreeder($Breeder);

        // breeder pet info template must be belongs to a breeder
        $this->assertInstanceOf(Breeders::class, $BreederPetinfoTemplate->getBreeder());
        $this->assertNotNull($BreederPetinfoTemplate->getBreeder());
        $this->expectException(TypeError::class);
        $BreederPetinfoTemplate->setBreeder(null);
    }

    public function testRequiredFields(): void
    {
        $BreederPetinfoTemplate = new BreederPetinfoTemplate;

        // below fields must have a value
        try {
            $BreederPetinfoTemplate->setBreeder(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederPetinfoTemplate->setDeliveryWay(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederPetinfoTemplate->setPaymentMethod(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
