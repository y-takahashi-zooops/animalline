<?php

namespace Customize\Tests\Service\Adoptions;

use Customize\Service\AdoptionQueryService;
use PHPUnit\Framework\TestCase;

class AdoptionsServiceTest extends TestCase
{
    private $adoptionsService;

    public function __construct()
    {
        parent::__construct();
        $this->adoptionsService = $this->createMock(AdoptionQueryService::class);
    }

    public function testGetBreedsHavePet(): void
    {
        $Adoptions = $this->adoptionsService->getBreedsHavePet(1);
        $this->assertNotNull($Adoptions);
    }

    public function testSearcPetsResult(): void
    {
        $Adoptions = $this->adoptionsService->searchPetsResult([]);
        $this->assertNotNull($Adoptions);
    }

    public function testsearchAdoptionsResult(): void
    {
        $Adoptions = $this->adoptionsService->searchAdoptionsResult([], 1);
        $this->assertNotNull($Adoptions);
    }

    public function testFilterPetAdmin(): void
    {
        $Adoptions = $this->adoptionsService->filterPetAdmin([], []);
        $this->assertNotNull($Adoptions);
    }
}
