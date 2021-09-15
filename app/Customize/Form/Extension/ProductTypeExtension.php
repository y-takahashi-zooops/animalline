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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductTypeExtension.
 */
class ProductTypeExtension extends AbstractTypeExtension
{

     /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
        ->add('quantity_box', NumberType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ])
        ->add('item_weight', NumberType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Range(['min' => 1, 'max' => 999.99]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }
}
