<?php

namespace Customize\Tests\Repository\Adoptions;

use Customize\Repository\ConservationsRepository;
use PHPUnit\Framework\TestCase;

class ConservationsRepositoryTest extends TestCase
{

    /**
     * Test search conservation with examination_status and organization_name
     *
     * @param array $request
     * @return array
     */
    public function testsearchConservations(): void
    {
        $conservationRepository = $this->createMock(ConservationsRepository::class);
        $request = $this->createMockQuery();

        $records = $conservationRepository->searchConservations($request);

        $this->assertNotNull($records);
    }

    /**
     * Create data
     *
     * @return array
     */
    private function createMockQuery(): array
    {
        $request = [
            "organization_name" => "breeder name",
            "examination_status" => 1,
            "field" => "create_date",
            "direction" => "DESC"
        ];

        return $request;
    }
}
