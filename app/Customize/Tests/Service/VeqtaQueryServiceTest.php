<?php

namespace Customize\Tests\Service;

use Customize\Service\VeqtaQueryService;
use PHPUnit\Framework\TestCase;

class VeqtaServiceTest extends TestCase
{
    private $veqtaService;

    public function __construct()
    {
        parent::__construct();
        $this->veqtaService = $this->createMock(VeqtaQueryService::class);
    }

    public function testFilterPetList(): void
    {
        $veqta = $this->veqtaService->filterPetList([]);
        $this->assertNotNull($veqta);
    }
}
