<?php

namespace Customize\Tests\FormType\Breeder;

use Customize\Entity\BreederEvaluations;
use Customize\Form\Type\Breeder\BreederEvaluationsType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManagerInterface;

class BreederEvalutionTypeTest extends TypeTestCase
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
        $type = new BreederEvaluationsType($this->entityManager);
        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension(Validation::createValidator())
        );
    }

    public function testSubmitValidData()
    {
        $formData = [
            'evaluation_value' => 1,
            'evaluation_title' => 'code',
            'evaluation_message' => 'abc',
            'thumbnail_path' => 'abc'
        ];

        $object = new BreederEvaluations();

        $form = $this->factory->create(BreederEvaluationsType::class, $object);

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