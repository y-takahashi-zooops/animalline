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

use Eccube\Form\Type\Admin\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductTypeExtension.
 */
final class ProductTypeExtension extends AbstractTypeExtension
{
    public function __construct()
    {
        // 明示的な空のコンストラクタ（Symfonyに「abstractではない」と認識させる）
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item_weight', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' =>  0.1, 'max' => 999.99]),
                ],
            ])
            ->add('is_check_auth', CheckboxType::class, [
                'required' => false,
                'label' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
