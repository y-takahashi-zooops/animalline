<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederEvaluations;
use Customize\Entity\BreederPets;
use PHPUnit\Framework\TestCase;
use TypeError;

class BreederEvaluationsTest extends TestCase
{
    public function testCreateNewBreeder(): void
    {
        $BreederEvaluation = new BreederEvaluations;
        $this->assertInstanceOf(BreederEvaluations::class, $BreederEvaluation);
    }

    public function testCreateBreederEvaluationsData(): void
    {
        $Pet = new BreederPets;
        $value = 1;
        $message = 'test message';
        $BreederEvaluation = (new BreederEvaluations)
            ->setPet($Pet)
            ->setEvaluationValue($value)
            ->setEvaluationMessage($message);

        $this->assertEquals(
            [$Pet, $value, $message],
            [$BreederEvaluation->getPet(), $BreederEvaluation->getEvaluationValue(), $BreederEvaluation->getEvaluationMessage()]
        );
    }

    public function testRelations(): void
    {
        $Pet = new BreederPets;
        $BreederEvaluation = (new BreederEvaluations)
            ->setPet($Pet);

        // breeder evaluations must be belongs to a pet
        $this->assertInstanceOf(BreederPets::class, $BreederEvaluation->getPet());
        $this->assertNotNull($BreederEvaluation->getPet());
        $this->expectException(TypeError::class);
        $BreederEvaluation->setPet(null);
    }

    public function testRequiredFields(): void
    {
        $BreederEvaluation = new BreederEvaluations;

        // below fields must have a value
        try {
            $BreederEvaluation->setPet(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederEvaluation->setEvaluationValue(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
        try {
            $BreederEvaluation->setEvaluationMessage(null);
        } catch (TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
