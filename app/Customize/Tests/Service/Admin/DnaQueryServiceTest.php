<?php

namespace Customize\Tests\Service\Admin;

use Customize\Service\DnaQueryService;
use PHPUnit\Framework\TestCase;

class DnaQueryServiceTest extends TestCase
{
    private $dnaService;

    public function __construct()
    {
        parent::__construct();
        $this->dnaService = $this->createMock(DnaQueryService::class);
    }

    public function testFilterDnaAdoptionMember(): void
    {
        $dna = $this->dnaService->filterDnaAdoptionMember(15, true);
        $this->assertNotNull($dna);
    }
}
