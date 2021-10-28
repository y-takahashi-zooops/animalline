<?php

namespace Customize\Tests\FormType\Breeder;

use Customize\Entity\BreederPets;
use Customize\Entity\Breeds;
use Customize\Entity\Pedigree;
use Customize\Form\Type\Breeder\BreederPetsType;
use Customize\Repository\BreedersRepository;
use DateTime;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class BreederPetsTypeTest extends TypeTestCase
{
    private $entityManager;

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = $this->createMock(BreedersRepository::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        // create a type instance with the mocked dependencies
        $type = new BreederPetsType($this->entityManager);
        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator())
        );
    }

    public function testSubmitValidData()
    {
        $formData = [
            'breeds_type' => new Breeds(),
            'pet_sex' => 1,
            'pet_birthday' => new DateTime(),
            'band_color' => 1,
            'coat_color' => 'red',
            'future_wait' => 1,
            'pr_comment' => 'abc',
            'description' => 'abc',
            'guarantee' => 'abc',
            'is_pedigree' => 'abc',
            'vaccine_detail' => 'abc',
            'pedigree_code' => 1,
            'include_vaccine_fee' => 1,
            'delivery_way' => 'abc',
            'payment_method' => 'abc',
            'reservation_fee' => 'abc',
            'thumbnail_path' => 'abc',
            'image1' => 'abc',
            'image2' => 'abc',
            'image3' => 'abc',
            'image4' => 'abc',
            'price' => 1,
            'pet_kind' => 1
        ];

        $object = new BreederPets();

        $form = $this->factory->create(BreederPetsType::class, $object);

        // submit the data to the form directly
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
