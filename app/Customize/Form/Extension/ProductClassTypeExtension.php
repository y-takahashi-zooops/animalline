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
        $suppliers = $this->supplierRepository->findAll();
        foreach ($suppliers as $supplier) {
            $choices[$supplier->getSupplierName()] = $supplier->getId();
        }

        $builder            
        ->add('supplier_code', ChoiceType::class, [
            'choices' => $choices,
            'placeholder' => 'common.select'
        ])
        ->add('item_cost', PriceType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
   }

   /**
    * {@inheritdoc}
    */
   public function getExtendedType()
   {
       return ProductClassType::class;
   }
}
