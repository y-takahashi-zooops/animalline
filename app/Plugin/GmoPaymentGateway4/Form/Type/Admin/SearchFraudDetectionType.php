<?php

/*
 * Copyright(c) 2022 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchFraudDetectionType extends AbstractType
{
    /**
     * @var Eccube\Common\EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * SearchGmoSubsCustomerType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        EccubeConfig $eccubeConfig
    ) {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prefix = 'gmo_payment_gateway.admin.fraud_detection.search.cond.';

        $builder
            // IPアドレス
            ->add('ip_address', TextType::class, [
                'label' => $prefix . 'ip_address',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len']
                    ]),
                ],
            ])
            // 発生日
            ->add('occur_time_start', DateType::class, [
                'label' => $prefix . 'occur_time_start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => '----', 'month' => '--', 'day' => '--'
                ],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' =>
                        '#'.$this->getBlockPrefix().'_occur_time_start',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            ->add('occur_time_end', DateType::class, [
                'label' => $prefix . 'occur_time_end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => '----', 'month' => '--', 'day' => '--'
                ],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' =>
                        '#'.$this->getBlockPrefix().'_occur_time_end',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            // エラー回数
            ->add('error_count_start', IntegerType::class, [
                'label' => $prefix . 'error_count_start',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_int_len']
                    ]),
                ],
            ])
            ->add('error_count_end', IntegerType::class, [
                'label' => $prefix . 'error_count_end',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_int_len']
                    ]),
                ],
            ])
            // 作成日付
            ->add('create_date_start', DateType::class, [
                'label' => 'admin.common.create_date__start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => '----', 'month' => '--', 'day' => '--'
                ],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' =>
                        '#'.$this->getBlockPrefix().'_create_date_start',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            ->add('create_date_end', DateType::class, [
                'label' => 'admin.common.create_date__end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => '----', 'month' => '--', 'day' => '--'
                ],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' =>
                        '#'.$this->getBlockPrefix().'_create_date_end',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            // 更新日付
            ->add('update_date_start', DateType::class, [
                'label' => 'admin.common.update_date__start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => '----', 'month' => '--', 'day' => '--'
                ],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' =>
                        '#'.$this->getBlockPrefix().'_update_date_start',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            ->add('update_date_end', DateType::class, [
                'label' => 'admin.common.update_date__end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => '----', 'month' => '--', 'day' => '--'
                ],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' =>
                        '#'.$this->getBlockPrefix().'_update_date_end',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_search_fraud_detection';
    }
}
