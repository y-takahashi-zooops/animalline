<?php

namespace Customize\Tests\FormType\Admin;

use Carbon\Carbon;
use Customize\Entity\InstockScheduleHeader;
use Customize\Form\Type\Admin\InstockScheduleHeaderType;
use Customize\Repository\SupplierRepository;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\OrderItem;
use Eccube\Form\Type\Admin\OrderItemType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Eccube\Repository\OrderItemRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\TaxRuleRepository;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class InstockScheduleHeaderTypeTest extends TypeTestCase
{
    private $entityManager;

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = $this->createMock(SupplierRepository::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->method('getClassMetadata')
            ->willReturn(new ClassMetadata(InstockScheduleHeaderType::class));

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
            $types = new InstockScheduleHeaderType($this->entityManager);
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
                        case OrderItemType::class:
                            return [new OrderItemType()];
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
        $date = new DateTime();
        $orderItem = new OrderItem;
        $form1 = $this->factory->create(OrderItemType::class, $orderItem);
        $formData = [
            'order_date' => $date,
            'supplier_code' => 1,
            'arrival_date_schedule' => $date,
            'remark_text' => 'abc',
            'InstockSchedule' => $form1
        ];

        $object = (new InstockScheduleHeader())
                    ->setOrderDate($date)
                    ->setSupplierCode(1)
                    ->setArrivalDateSchedule($date)
                    ->setRemarkText('abc')
                    ->setInstockSchedule(OrderItemType::class);

        $form = $this->factory->create(InstockScheduleHeaderType::class);

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
