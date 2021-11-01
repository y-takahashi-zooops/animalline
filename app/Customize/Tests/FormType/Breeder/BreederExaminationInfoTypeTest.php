<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\BreederExaminationInfo;
use Customize\Entity\Pedigree;
use Customize\Form\Type\Breeder\BreederExaminationInfoType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Eccube\Common\EccubeConfig;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class BreederExaminationInfoTypeTest extends TypeTestCase
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
            ->willReturn(new ClassMetadata(BreederExaminationInfoType::class));

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
            $types = new BreederExaminationInfoType($this->entityManager);
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
                        case Pedigree::class:
                            return [new Pedigree()];
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
            'pedigree_organization' => 1,
            'pedigree_organization_other' => 'abc',
            'parent_pet_count_1' => 1,
            'parent_pet_count_2' => 1,
            'parent_pet_count_3' => 1,
            'parent_pet_buy_place_1' => 1,
            'parent_pet_buy_place_2' => 1,
            'parent_pet_buy_place_3' => 1,
            'owner_worktime_ave' => 1,
            'family_staff_count' => 1,
            'family_staff_worktime_ave' => 1,
            'fulltime_staff_count' => 1,
            'parttime_staff_count' => 1,
            'parttime_staff_worktime_ave' => 1,
            'other_staff_count' => 1,
            'other_staff_worktime_ave' => 1,
            'breeding_exp_year' => 1,
            'breeding_exp_month' => 1,
            'cage_size_1' => 0,
            'cage_size_2' => 0,
            'cage_size_3' => 0,
            'cage_size_other' => 'abc',
            'exercise_status' => 1,
            'exercise_status_other' => 'abc',
            'publish_pet_count' => 1,
            'breeding_experience' => 1,
            'selling_experience' => 1
        ];

        $object = new BreederExaminationInfo();

        $form = $this->factory->create(BreederExaminationInfoType::class, $object);

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
