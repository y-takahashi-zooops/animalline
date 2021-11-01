<?php

namespace Customize\Tests\Breeders;

use Customize\Repository\BreedersRepository;
use PHPUnit\Framework\TestCase;

class BreedersRepositoryTest extends TestCase
{
    public function testFilterBreederAdmin(): void
    {
        $breedersRepository = $this->createMock(BreedersRepository::class);
        [$criteria, $sort] = $this->createMockQuery();

        $records = $breedersRepository->filterBreederAdmin($criteria, $sort);

        $this->assertNotNull($records);
    }

    // test data
    private function createMockQuery(): array
    {
        $criteria = [
            "breeder_name" => "breeder name"
        ];
        $sort = [
            "field" => "create_date",
            "direction" => "DESC"
        ];

        return [$criteria, $sort];
    }
}
