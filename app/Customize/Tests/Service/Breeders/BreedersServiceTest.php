<?php

namespace Customize\Tests\Breeders;

use Customize\Service\BreederQueryService;
use PHPUnit\Framework\TestCase;

class BreedersServiceTest extends TestCase
{
    private $breedersService;

    public function __construct()
    {
        parent::__construct();
        $this->breedersService = $this->createMock(BreederQueryService::class);
    }

    public function testGetBreedsHavePet(): void
    {
        $Breeds = $this->breedersService->getBreedsHavePet(1);
        $this->assertNotNull($Breeds);
    }

    public function testSearcPetsResult(): void
    {
        $Breeds = $this->breedersService->searchPetsResult([]);
        $this->assertNotNull($Breeds);
    }

    public function testSearchBreedersResult(): void
    {
        $Breeds = $this->breedersService->searchBreedersResult([], 1);
        $this->assertNotNull($Breeds);
    }

    public function testFindBreederFavoritePets(): void
    {
        $Breeds = $this->breedersService->findBreederFavoritePets(1);
        $this->assertNotNull($Breeds);
        $this->assertInternalType('array', $Breeds);
    }

    public function testCalBreederRank(): void
    {
        $Breeds = $this->breedersService->calculateBreederRank(1);
        $this->assertNotNull($Breeds);
    }

    public function testFilterPetAdmin(): void
    {
        $Breeds = $this->breedersService->filterPetAdmin([], []);
        $this->assertNotNull($Breeds);
    }
}
