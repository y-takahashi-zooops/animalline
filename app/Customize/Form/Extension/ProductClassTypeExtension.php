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

use Eccube\Form\Type\Admin\ProductClassType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\FormBuilderInterface;
use Customize\Repository\SupplierRepository;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductClassTypeExtension.
 */
class ProductClassTypeExtension extends AbstractTypeExtension
{

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // 仕入先ドロップダウン
        $choices = [];
        $suppliers = $this->supplierRepository->findAll();
        foreach ($suppliers as $supplier) {
            $choices[$supplier->getSupplierName()] = $supplier->getId();
        }

        $builder
            ->add('code', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('supplier_code', ChoiceType::class, [
                'choices' => $choices,
                'placeholder' => 'common.select'
            ])
            ->add('jan_code', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'max' => 13,
                    ]),
                ]
            ])
            ->add('stock_code', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 5,
                        'max' => 5,
                    ]),
                    new Assert\NotBlank(),
                    new Assert\Regex(['pattern' => '/^\d{5}$/']),
                ]
            ])
            ->add('item_cost', PriceType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('incentive_ratio', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                    ]),
                    new Assert\NotBlank(),
                    new Assert\LessThanOrEqual([
                        'value' => 100
                    ])
                ]
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ProductClassType::class];
    }
}
