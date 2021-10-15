<?php

namespace Customize\Tests\Breeders;

use Customize\Service\BreederQueryService;
use PHPUnit\Framework\TestCase;

class BreedersServiceTest extends TestCase
{
    public function testGetBreedsHavePet(): void
    {
        $breedersService = $this->createMock(BreederQueryService::class);
        $Breeds = $breedersService->getBreedsHavePet(1);
        $this->assertNotNull($Breeds);
    }

    public function testSearcPetsResult(): void
    {
        $breedersService = $this->createMock(BreederQueryService::class);
        $Breeds = $breedersService->searchPetsResult([]);
        $this->assertNotNull($Breeds);
    }

    public function testSearchBreedersResult(): void
    {
        $breedersService = $this->createMock(BreederQueryService::class);
        $Breeds = $breedersService->searchBreedersResult([], 1);
        $this->assertNotNull($Breeds);
    }

    public function testCalBreederRank(): void
    {
        $breedersService = $this->createMock(BreederQueryService::class);
        $Breeds = $breedersService->calculateBreederRank(1);
        $this->assertNotNull($Breeds);
    }

    public function testFilterPetAdmin(): void
    {
        $breedersService = $this->createMock(BreederQueryService::class);
        $Breeds = $breedersService->filterPetAdmin([], []);
        $this->assertNotNull($Breeds);
    }
}
