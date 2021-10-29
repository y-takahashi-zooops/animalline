<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\BreederPets;
use Customize\Entity\Pedigree;
use Customize\Form\Type\Admin\BreederPetsType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class BreederPetTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $Pedigree = (new Pedigree())
                ->setPetKind(1)
                ->setPedigreeName('abc');
        $formData = [
            'pet_sex' => 1,
            'pet_birthday' => new DateTime(),
            'band_color' => 1,
            'coat_color' => 'red',
            'future_wait' => 1,
            'dna_check_result' => 1,
            'pr_comment' => 'abc',
            'description' => 'abc',
            'guarantee' => 'abc',
            'is_pedigree' => 'abc',
            'Pedigree' => $Pedigree,
            'pedigree_code' => 'abc',
            'microchip_code' => 1,
            'include_vaccine_fee' => 1,
            'delivery_way' => 'abc',
            'price' => 464856
        ];

        $object = (new BreederPets())
                    ->setPedigreeCode('abc')
                    ->setMicrochipCode(1)
                    ->setBandColor(1)
                    ->setIncludeVaccineFee(1)
                    ->setGuarantee('abc')
                    ->setPrice(464856)
                    ->setDeliveryWay('abc')
                    ->setPetSex(1)
                    ->setCoatColor('red')
                    ->setDescription('abc')
                    ->setFutureWait(1)
                    ->setDnaCheckResult(1)
                    ->setPrComment('abc');

        $form = $this->factory->create(BreederPetsType::class);

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

    protected function getExtensions(): array
    {
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->method('getClassMetadata')
            ->willReturn(new ClassMetadata(BreederPetsType::class));

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
            new ValidatorExtension(Validation::createValidator())
        ];
    }
}
