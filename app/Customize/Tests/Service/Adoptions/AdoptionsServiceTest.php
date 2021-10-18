<?php

namespace Customize\Tests\Service\Adoptions;

use Customize\Service\AdoptionQueryService;
use PHPUnit\Framework\TestCase;

class AdoptionsServiceTest extends TestCase
{
    public function testGetBreedsHavePet(): void
    {
        $adoptionsService = $this->createMock(AdoptionQueryService::class);
        $Breeds = $adoptionsService->getBreedsHavePet(1);
        $this->assertNotNull($Breeds);
    }

    public function testSearcPetsResult(): void
    {
        $adoptionsService = $this->createMock(AdoptionQueryService::class);
        $Breeds = $adoptionsService->searchPetsResult([]);
        $this->assertNotNull($Breeds);
    }

    public function testsearchAdoptionsResult(): void
    {
        $adoptionsService = $this->createMock(AdoptionQueryService::class);
        $Breeds = $adoptionsService->searchAdoptionsResult([], 1);
        $this->assertNotNull($Breeds);
    }

    public function testFilterPetAdmin(): void
    {
        $adoptionsService = $this->createMock(AdoptionQueryService::class);
        $Breeds = $adoptionsService->filterPetAdmin([], []);
        $this->assertNotNull($Breeds);
    }
}
