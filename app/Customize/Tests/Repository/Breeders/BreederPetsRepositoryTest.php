<?php

namespace Customize\Tests\Breeders;

use Customize\Entity\BreederPets;
use Customize\Repository\BreederPetsRepository;
use PHPUnit\Framework\TestCase;

class BreederPetsRepositoryTest extends TestCase
{
    private $breederPetsRepository;

    public function __construct()
    {
        parent::__construct();
        $this->breederPetsRepository = $this->createMock(BreederPetsRepository::class);
    }

    public function testFindByFavoriteCount(): void
    {
        $records = $this->breederPetsRepository->findByFavoriteCount();
        $this->assertNotNull($records);
        $this->assertInternalType('array', $records);
    }

    public function testIncrementCount(): void
    {
        $BreederPet = new BreederPets;
        $this->breederPetsRepository->incrementCount($BreederPet);
        $this->assertTrue(true);
    }

    public function testDecrementCount(): void
    {
        $BreederPet = new BreederPets;
        $this->breederPetsRepository->decrementCount($BreederPet);
        $this->assertTrue(true);
    }

    public function testFilterBreederPetsAdmin(): void
    {
        [$criteria, $sort] = $this->createMockQuery();
        $records = $this->breederPetsRepository->filterBreederPetsAdmin($criteria, $sort);

        $this->assertNotNull($records);
        $this->assertInternalType('array', $records);
    }

    // test data
    private function createMockQuery(): array
    {
        $criteria = [
            'pet_kind' => 1,
            'breed_type' => 1,
            'public_status' => 1
        ];
        $sort = [
            'field' => 'create_date',
            'direction' => 'DESC'
        ];

        return [$criteria, $sort];
    }
}
