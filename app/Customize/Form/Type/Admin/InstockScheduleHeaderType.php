<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\InstockSchedule;
use Customize\Entity\InstockScheduleHeader;
use Customize\Entity\Supplier;
use Doctrine\DBAL\Types\DateType;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\InstockScheduleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InstockScheduleHeaderType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order_date', DateType::class, [
                'placeholder' => '',
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('supplier_code', EntityType::class, [
                'class' => Supplier::class,
//                'choice' => ,
                'choice_label' => function (Supplier $supplier) {
                    return $supplier->getSupplierName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('arrival_date_schedule', DateType::class, [
                'placeholder' => '',
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('remark_text', TextareaType::class, [
                'required' => false,
            ])
            ->add('instockSchedules', CollectionType::class, [
                'entry_type' => InstockScheduleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InstockScheduleHeader::class,
        ]);
    }
}
