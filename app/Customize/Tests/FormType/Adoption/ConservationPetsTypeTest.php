<?php

namespace Customize\Tests\FormType\Adoption;

use Customize\Entity\Breeds;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationPets;
use Customize\Entity\ConservationsHouse;
use Customize\Form\Type\Adoption\ConservationContactType;
use Customize\Form\Type\Adoption\ConservationHouseType;
use Customize\Form\Type\Adoption\ConservationPetsType;
use Customize\Tests\Controller\Breeders\BreederPetControllerTest;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\Pref;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Validator\Validation;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use DateTime;

class ConservationPetsTypeTest extends TypeTestCase
{
    private $EccubeConfig;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        $this->EccubeConfig = $this->createMock(EccubeConfig::class);
        $this->em = DoctrineTestHelper::createTestEntityManager();

        parent::setUp();
    }

    protected function getExtensions()
    {
        $type = new ConservationPetsType($this->EccubeConfig);

        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator()),
        );
    }

    public function testSubmitValidData()
    {
        $Breed = new Breeds();
        $formData = [
            'pet_kind' => 1,
            'BreedsType' => $Breed,
            'pet_sex' => 1,
            'pet_birthday' => new DateTime(),
            'coat_color' => "test",
            'future_wait' => 1,
            'pr_comment' => "test",
            'description' => "test",
            'delivery_time' => "test",
            'delivery_way' => "test",
            'thumbnail_path' => "url",
        ];

        $object = (new ConservationPets())
            ->setPetKind(1)
            ->setBreedsType($Breed)
            ->setPetSex(1)
            ->setPetBirthday(new DateTime())
            ->setCoatColor("test")
            ->setFutureWait(1)
            ->setPrComment("test")
            ->setDescription("test")
            ->setDeliveryTime("test")
            ->setDeliveryWay("test")
            ->setThumbnailPath("url");

        $form = $this->factory->create(ConservationPetsType::class, $object);

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
