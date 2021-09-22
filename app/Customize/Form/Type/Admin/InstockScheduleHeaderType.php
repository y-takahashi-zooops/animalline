<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\InstockScheduleHeader;
use Customize\Repository\SupplierRepository;
use Eccube\Common\EccubeConfig;
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
    )
    {
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
                'placeholder' => '',
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('supplier_code', ChoiceType::class, [
                'choices' => $choices,
                'placeholder' => 'common.select'
            ])
            ->add('arrival_date_schedule', DateType::class, [
                'placeholder' => '',
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('remark_text', TextareaType::class, [
                'required' => false,
            ]);
//            ->add('instockSchedules', CollectionType::class, [
//                'entry_type' => InstockScheduleType::class,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'prototype' => true,
//            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InstockScheduleHeader::class,
        ]);
    }
}
