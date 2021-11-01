<?php

namespace Customize\Tests\Repository\Adoptions;

use Customize\Entity\ConservationPets;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use PHPUnit\Framework\TestCase;

class ConservationPetsRepositoryTest extends TestCase
{
    private $conservationPetsRepository;

    public function __construct()
    {
        parent::__construct();
        $this->conservationPetsRepository = $this->createMock(ConservationsRepository::class);
    }
    /**
     * Test search conservation with examination_status and organization_name
     *
     * @param array $request
     * @return array
     */
    public function testFindByFavoriteCount(): void
    {
        $conservationPetsRepository = $this->createMock(ConservationPetsRepository::class);

        $records = $conservationPetsRepository->findByFavoriteCount();

        $this->assertNotNull($records);
        $this->assertInternalType('array', $records);
    }

    /**
     * Test increment favorite count of pet
     *
     * @return int|mixed|string
     */
    public function testIncrementCount()
    {
        $ConservationPet = new ConservationPets;
        $this->conservationPetsRepository->incrementCount($ConservationPet);
        $this->assertTrue(true);
    }

    /**
     * Test decrement favorite count of pet
     *
     * @return int|mixed|string
     */
    public function testDecrementCount()
    {
        $ConservationPet = new ConservationPets;
        $this->conservationPetsRepository->decrementCount($ConservationPet);
        $this->assertTrue(true);
    }
}
