<?php

namespace Customize\Tests\Entity\Admin;

use Customize\Entity\Breeds;
use Customize\Entity\DnaCheckKinds;
use Customize\Entity\DnaCheckStatus;
use Customize\Entity\DnaCheckStatusDetail;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class DnaCheckKindsTest extends TestCase
{
    /**
     * Test create new record
     *
     * @return void
     */
    public function testCreateNewDnaCheckKinds(): void
    {
        $dnaCheckKinds = new DnaCheckKinds();
        $this->assertInstanceOf(DnaCheckKinds::class, $dnaCheckKinds);
    }

    /**
     * Test create with data
     *
     * @return void
     */
    public function testCreateDnaDnaCheckKindsData(): void
    {
        $dnaCheckKinds = new DnaCheckKinds();
        $Breeds = new Breeds();

        $dnaCheckKinds->setBreeds($Breeds);

        $this->assertEquals([
            $Breeds
        ],
            [
                $dnaCheckKinds->getBreeds(),
            ]
        );
    }

    /**
     * Test relation
     *
     * @return void
     */
    public function testRelations(): void
    {
        $dnaCheckKinds = new DnaCheckKinds();

        // Conservation contact header can have many
        $this->assertInstanceOf(Collection::class, $dnaCheckKinds->getCheckStatusDetails());

        // Conservation contact header can have one
        $this->assertNull($dnaCheckKinds->getBreeds());
    }

    /**
     * Test integer field
     *
     * @return void
     */
    public function testInteger(): void
    {
        $dnaCheckKinds = new DnaCheckKinds();
        $integerVal = 1;
        $dnaCheckKinds->setDeleteFlg($integerVal);

        $this->assertEquals($integerVal, $dnaCheckKinds->getDeleteFlg());
    }

    /**
     * Test default is true
     *
     * @return void
     */
    public function testDefaulValues(): void
    {
        $dnaCheckKinds = new DnaCheckKinds();

        // below fields must have a default value
        $this->assertEquals(0, $dnaCheckKinds->getDeleteFlg());

    }
}
