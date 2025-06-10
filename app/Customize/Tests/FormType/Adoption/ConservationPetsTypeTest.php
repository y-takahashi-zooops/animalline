<?php

namespace Customize\Tests\FormType\Adoption;

use Customize\Entity\ConservationPets;
use Customize\Form\Type\Adoption\ConservationPetsType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use DateTime;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManagerInterface;


class ConservationPetsTypeTest extends TypeTestCase
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }
        

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = $this->createMock(EccubeConfig::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $type = new ConservationPetsType($this->entityManager);
        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator())
        );
    }


    public function testSubmitValidData()
    {
        $formData = [
            'pet_kind' => 1,
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

        $object = new ConservationPets();

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
