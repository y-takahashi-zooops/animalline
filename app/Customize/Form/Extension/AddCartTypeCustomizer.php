<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Form\Extension;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\CartItem;
use Eccube\Entity\ProductClass;
use Eccube\Form\DataTransformer\EntityToIdTransformer;
use Eccube\Repository\ProductClassRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Form\AbstractTypeExtension;
use Eccube\Form\Type\AddCartType;

class AddCartTypeCustomizer extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $config;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Eccube\Entity\Product
     */
    protected $Product = null;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine, EccubeConfig $config)
    {
        $this->doctrine = $doctrine;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $Product \Eccube\Entity\Product */
        $Product = $options['product'];
        $this->Product = $Product;
        $ProductClasses = $Product->getProductClasses();

        $builder
            ->add('product_id', HiddenType::class, [
                'data' => $Product->getId(),
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex(['pattern' => '/^\d+$/']),
                ],
            ])
            ->add(
                $builder
                    ->create('ProductClass', HiddenType::class, [
                        'data_class' => null,
                        'data' => $Product->hasProductClass() ? null : $ProductClasses->first(),
                        'constraints' => [
                            new Assert\NotBlank(),
                        ],
                    ])
                    ->addModelTransformer(new EntityToIdTransformer($this->doctrine->getManager(), ProductClass::class))
            );

        if ($Product->getStockFind()) {
            $builder
                // 通常注文個数
                ->add('normal_quantity', IntegerType::class, [
                    'data' => 1,
                    'attr' => [
                        'min' => 1,
                        'maxlength' => $this->config['eccube_int_len'],
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\GreaterThanOrEqual([
                            'value' => 1,
                        ]),
                        new Assert\Regex(['pattern' => '/^\d+$/']),
                    ],
                    'mapped' => false,
                ])
                // 定期注文個数
                ->add('subscription_quantity', IntegerType::class, [
                    'data' => 1,
                    'attr' => [
                        'min' => 1,
                        'maxlength' => $this->config['eccube_int_len'],
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\GreaterThanOrEqual([
                            'value' => 1,
                        ]),
                        new Assert\Regex(['pattern' => '/^\d+$/']),
                    ],
                    'mapped' => false,
                ])
                // 注文種別ラジオボタン
                ->add('order_type', ChoiceType::class, [
                    'choices' => [
                        '通常注文' => 'normal',
                        '定期便' => 'subscription'
                    ],
                    'data' => 'normal',
                    'expanded' => true,
                    'multiple' => false,
                    'mapped' => false,
                ])
                // 間隔ラジオボタン
                ->add('cycle_type', ChoiceType::class, [
                    'choices' => [
                        '月間隔で指定' => 'month',
                        '日間隔で指定' => 'day'
                    ],
                    'data' => 'month',
                    'expanded' => true,
                    'multiple' => false,
                    'mapped' => false,
                ])
                // 月間隔
                ->add('month', ChoiceType::class, [
                    'choices'  =>
                    [
                        '毎月' => 1,
                        '２ヶ月毎' => 2,
                        '３ヶ月毎' => 3,
                        '４ヶ月毎' => 4,
                        '５ヶ月毎' => 5,
                        '６ヶ月毎' => 6
                    ],
                    'placeholder' => false,
                    'mapped' => false,
                ])
                // 日間隔
                ->add('day', IntegerType::class, [
                    'data' => 10,
                    'attr' => [
                        'min' => 10,
                        'max' => 99,
                        'maxlength' => $this->config['eccube_int_len'],
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\GreaterThanOrEqual([
                            'value' => 10,
                        ]),
                        new Assert\Regex(['pattern' => '/^\d+$/']),
                    ],
                    'mapped' => false,
                ])
                // 個数
                ->add('quantity', HiddenType::class, [
                    'mapped' => true,
                ])
                // 定期注文フラグ
                ->add('is_repeat', HiddenType::class, [
                    'mapped' => true,
                ])
                // 購入スパン
                ->add('repeat_span', HiddenType::class, [
                    'mapped' => true,
                ])
                // 購入スパン単位
                ->add('span_unit', HiddenType::class, [
                    'mapped' => true,
                ]);

            if ($Product && $Product->getProductClasses()) {
                if (!is_null($Product->getClassName1())) {
                    $builder->add('classcategory_id1', ChoiceType::class, [
                        'label' => $Product->getClassName1(),
                        'choices' => ['common.select' => '__unselected'] + $Product->getClassCategories1AsFlip(),
                        'mapped' => false,
                    ]);
                }
                if (!is_null($Product->getClassName2())) {
                    $builder->add('classcategory_id2', ChoiceType::class, [
                        'label' => $Product->getClassName2(),
                        'choices' => ['common.select' => '__unselected'],
                        'mapped' => false,
                    ]);
                }
            }

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($Product) {
                $data = $event->getData();
                $form = $event->getForm();
                if (isset($data['classcategory_id1']) && !is_null($Product->getClassName2())) {
                    if ($data['classcategory_id1']) {
                        $form->add('classcategory_id2', ChoiceType::class, [
                            'label' => $Product->getClassName2(),
                            'choices' => ['common.select' => '__unselected'] + $Product->getClassCategories2AsFlip($data['classcategory_id1']),
                            'mapped' => false,
                        ]);
                    }
                }
                // 定期注文の場合
                if ($data['order_type'] == 'subscription') {
                    $isRepeat = 1;
                    // 日間隔の場合、日を購入スパンに設定
                    if ($data['cycle_type'] == 'day') {
                        $spanUnit = 0;
                        $repeatSpan = $data['day'];
                        // 月間隔の場合、月を購入スパンに設定
                    } elseif ($data['cycle_type'] == 'month') {
                        $spanUnit = 1;
                        $repeatSpan = $data['month'];
                    }

                    $data['quantity'] = $data['subscription_quantity'];
                    $data['is_repeat'] = $isRepeat;
                    $data['span_unit'] = $spanUnit;
                    $data['repeat_span'] = $repeatSpan;
                // 通常注文の場合
                } else {
                    $data['quantity'] = $data['normal_quantity'];
                }
                $event->setData($data);
            });

            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var CartItem $CartItem */
                $CartItem = $event->getData();
                $ProductClass = $CartItem->getProductClass();
                // FIXME 価格の設定箇所、ここでいいのか
                if ($ProductClass) {
                    $CartItem
                        ->setProductClass($ProductClass)
                        ->setPrice($ProductClass->getPrice02IncTax());
                }

                $data = $event->getData();
                $CartItem
                    ->setQuantity($data['quantity'])
                    ->setIsRepeat($data['is_repeat'])
                    ->setSpanUnit($data['span_unit'])
                    ->setRepeatSpan($data['repeat_span']);
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('product');
        $resolver->setDefaults([
            'data_class' => CartItem::class,
            'id_add_product_id' => true,
            'constraints' => [
                // FIXME new Assert\Callback(array($this, 'validate')),
            ],
        ]);
    }

    /*
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['id_add_product_id']) {
            foreach ($view->vars['form']->children as $child) {
                $child->vars['id'] .= $options['product']->getId();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'add_cart';
    }

    /**
     * validate
     *
     * @param type $data
     * @param ExecutionContext $context
     */
    public function validate($data, ExecutionContext $context)
    {
        $context->getValidator()->validate($data['product_class_id'], [
            new Assert\NotBlank(),
        ], '[product_class_id]');
        if ($this->Product->getClassName1()) {
            //$context->validateValue($data['classcategory_id1'], [
            $context->getValidator()->validate($data['classcategory_id1'], [
                new Assert\NotBlank(),
                new Assert\NotEqualTo([
                    'value' => '__unselected',
                    'message' => 'form_error.not_selected',
                ]),
            ], '[classcategory_id1]');
        }
        //商品規格2初期状態(未選択)の場合の返却値は「NULL」で「__unselected」ではない
        if ($this->Product->getClassName2()) {
            $context->getValidator()->validate($data['classcategory_id2'], [
                new Assert\NotBlank(),
                new Assert\NotEqualTo([
                    'value' => '__unselected',
                    'message' => 'form_error.not_selected',
                ]),
            ], '[classcategory_id2]');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [\Eccube\Form\Type\AddCartType::class];
    }

    /**
     * @deprecated For Symfony < 4.2 compatibility
     */
    public static function getExtendedType()
    {
        return \Eccube\Form\Type\AddCartType::class;
    }

}
