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

namespace Customize\Form\Type\Breeder;

use Customize\Entity\DnaCheckStatusHeader;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BreederKitDnaType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ContactType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('choice_address', ChoiceType::class, [
                'required' => true,
                'mapped' => false,
                'choices' =>
                    [
                        '基本情報' => 1,
                        '犬舎住所' => 2,
                        '猫舎住所' => 3,
                    ],
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank()
                ],
            ])
            ->add('shipping_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'readonly' => true,
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('shipping_zip', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Length([
                        'max' => 7,
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'readonly' => true,
                    'class' => 'p-postal-code',
                    'placeholder' => 'common.postal_code_sample',
                ],
                'trim' => true,
            ])
            ->add('address', AddressHouseKitType::class, [
                'attr' => [
                    'readonly' => true,
                ]])
            ->add('shipping_tel', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 11,
                ],
                'trim' => true,
            ])
            ->add('kit_unit', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 1,
                    ]),
                    new Assert\NotBlank(),
                    new Assert\LessThanOrEqual([
                        'value' => 6
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DnaCheckStatusHeader::class,
        ]);
    }
}
