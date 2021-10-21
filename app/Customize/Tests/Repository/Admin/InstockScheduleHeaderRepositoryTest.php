<?php

namespace Customize\Tests\Repository\Admin;

use Customize\Repository\InstockScheduleHeaderRepository;
use PHPUnit\Framework\TestCase;

class InstockScheduleHeaderRepositoryTest extends TestCase
{
    /**
     * Test search
     *
     * @return void
     * @throws \Exception
     */
    public function testSearch(): void
    {
        $instockScheduleHeaderRepository = $this->createMock(InstockScheduleHeaderRepository::class);
        [$orderDate, $scheduleDate] = $this->createMockQuery();
        $records = $instockScheduleHeaderRepository->search($orderDate, $scheduleDate);
        $this->assertNotNull($records);
        $this->assertInternalType('array', $records);
    }

    private function createMockQuery(): array
    {
        $orderDate = [
            "orderDateYear" => "2016",
            "orderDateMonth" => "01",
            'orderDateDay' => "01",
        ];
        $scheduleDate = [
            "scheduleDateYear" => "2020",
            "scheduleDateMonth" => "9",
            "scheduleDateDay" => "29",
        ];

        return [$orderDate, $scheduleDate];
    }
}
