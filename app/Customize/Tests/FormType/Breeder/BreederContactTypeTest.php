<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\BreederContactHeader;
use Customize\Form\Type\Breeder\BreederContactType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManagerInterface;

class BreederContactTypeTest extends TypeTestCase
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
        // create a type instance with the mocked dependencies
        $type = new BreederContactType($this->entityManager);
        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator())
        );
    }

    public function testSubmitValidData()
    {
        $formData = [
            'contact_type' => 1,
            'contact_title' => 'code',
            'contact_description' => 'abc',
            'booking_request' => 'abc'
        ];

        $object = new BreederContactHeader();

        $form = $this->factory->create(BreederContactType::class, $object);

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