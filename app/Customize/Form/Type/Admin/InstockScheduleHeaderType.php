<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\InstockScheduleHeader;
use Customize\Repository\SupplierRepository;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\OrderItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    public function __construct(
        EccubeConfig       $eccubeConfig,
        SupplierRepository $supplierRepository
    ) {
        $this->eccubeConfig = $eccubeConfig;
        $this->supplierRepository = $supplierRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // 仕入先ドロップダウン
        $choices = [];
        $suppliers = $this->supplierRepository->findAll();
        foreach ($suppliers as $supplier) {
            $choices[$supplier->getSupplierName()] = $supplier->getSupplierCode();
        }

        $builder
            ->add('order_date', DateType::class, [
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('supplier_code', ChoiceType::class, [
                'placeholder' => 'common.select',
                'choices' => $choices,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('arrival_date_schedule', DateType::class, [
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('remark_text', TextareaType::class, [
                'required' => false,
            ])
            ->add('InstockSchedule', CollectionType::class, [
                'entry_type' => OrderItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'mapped' => $options['isEdit'] // only map when edit
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InstockScheduleHeader::class,
            'isEdit' => false
        ]);

        $resolver->setRequired('isEdit');
        $resolver->setAllowedTypes('isEdit', 'bool');
    }
}
