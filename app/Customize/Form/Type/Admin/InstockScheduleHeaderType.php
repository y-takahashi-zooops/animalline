<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\InstockScheduleHeader;
use Customize\Repository\SupplierRepository;
use DateTime;
use Eccube\Form\Type\Admin\OrderItemType;
use Eccube\Form\Type\Admin\InstockScheduleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class InstockScheduleHeaderType extends AbstractType
{

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    public function __construct(
        SupplierRepository $supplierRepository
    ) {
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
                'years' => range(date('Y')-1, date('Y')),
                'format' => 'yyyy-MM-dd',
                'required' => true,
                //'data' => new DateTime(),
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
                'years' => range(date('Y')-1, date('Y')),
                'format' => 'yyyy-MM-dd',
                'required' => true,
                //'data' => new DateTime(),
            ])
            ->add('remark_text', TextareaType::class, [
                'required' => false,
            ])
            ->add('is_commit', ChoiceType::class, [
                'choices' => [
                    '在庫に実績を反映しない' => 0,
                    '在庫に実績を反映する' => 1,
                ],
                'expanded' => false,
                'required' => true,
            ])
            ->add('InstockSchedule', CollectionType::class, [
                'entry_type' => InstockScheduleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'mapped' => $options['isEdit'] // only map when edit
            ])
            ->add('InstockScheduleErrors', TextType::class, [
                'mapped' => false,
            ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateInstockSchedule']);
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

    /**
     * 受注明細のバリデーションを行う.
     * 商品明細が1件も登録されていない場合はエラーとする.
     *
     * @param FormEvent $event
     */
    public function validateInstockSchedule(FormEvent $event)
    {
        $form = $event->getForm();
        if (count($form['InstockSchedule']) < 1) {
            $form['InstockScheduleErrors']->addError(new FormError(trans('admin.order.product_item_not_found')));
        }

    }
}
