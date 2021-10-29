<?php

namespace Customize\Tests\FormType\Adoption;

use Customize\Entity\Conservations;
use Customize\Form\Type\Adoption\ConservationHouseType;
use Customize\Form\Type\Adoption\ConservationsType;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\Pref;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\FormExtensionInterface;

class ConservationsTypeTest extends TypeTestCase
{
    private $entityManager;

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = $this->createMock(EccubeConfig::class);

        parent::setUp();
    }
    protected function getExtensions(): array
    {
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->method('getClassMetadata')
            ->willReturn(new ClassMetadata(ConservationsType::class));

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($mockEntityManager));
        $mockEntityManager->method('getRepository')->willReturn($entityRepository);

        $mockRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getManagerForClass'])
            ->getMock();

        $mockRegistry->method('getManagerForClass')
            ->willReturn($mockEntityManager);

        /** @var EntityType|\PHPUnit_Framework_MockObject_MockObject $mockEntityType */
        $mockEntityType = $this->getMockBuilder(EntityType::class)
            ->setConstructorArgs([$mockRegistry])
            ->setMethodsExcept(['configureOptions', 'getParent'])
            ->getMock();
        $types = new ConservationsType($this->entityManager);
        $mockEntityType->method('getLoader')->willReturnCallback(function ($a, $b, $class) {
            return new class($class) implements EntityLoaderInterface
            {
                private $class;

                public function __construct($class)
                {
                    $this->class = $class;
                }

                public function getEntities()
                {
                    switch ($this->class) {
                        case Pref::class:
                            return [new Pref()];
                            break;
                    }
                }

                public function getEntitiesByIds($identifier, array $values)
                {
                    // TODO: implement
                }
            };
        });

        return [
            new class($mockEntityType) implements FormExtensionInterface
            {
                private $type;

                public function __construct($type)
                {
                    $this->type = $type;
                }

                public function getType($name)
                {
                    return $this->type;
                }

                public function hasType($name)
                {
                    return $name === EntityType::class;
                }

                public function getTypeExtensions($name)
                {
                    return [];
                }

                public function hasTypeExtensions($name)
                {
                }

                public function getTypeGuesser()
                {
                }
            },
            new PreloadedExtension(array($types), array()),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'is_organization' => 1,
            'organization_name' => "test",
            'handling_pet_kind' => 1,
            'owner_name' => "test",
            'owner_kana' => "test",
            'city' => "test",
            'address' => "test",
            'zip' => "test",
            'tel' => "test",
            'fax' => "test",
            'homepage_url' => "url",
            'pr_text' => "test",
        ];

        $object = new Conservations();

        $form = $this->factory->create(ConservationsType::class, $object);

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
