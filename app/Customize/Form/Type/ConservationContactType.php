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

namespace Customize\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Eccube\Common\EccubeConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationContactType extends AbstractType
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
            ->add('conservation', IntegerType::class, [
                'required' => false,
            ])
            ->add('message_from', ChoiceType::class, [
                'choices' =>
                    [
                        'ユーザー' => 1,
                        '保護団体' => 2,
                    ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('pet', IntegerType::class, [
                'required' => false,
            ])
            ->add('contact_type', ChoiceType::class, [
                'choices' =>
                    [
                        '問い合わせ' => 1,
                        '見学希望' => 2,
                        '返信' => 3,
                    ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('contact_title', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('contact_description', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('booking_request', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('parent_message_id', ChoiceType::class, [
                'choices' =>
                    [
                        '新規' => 0,
                    ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('send_date', DateTimeType::class, [
                'required' => true,
            ])
            ->add('is_response', ChoiceType::class, [
                'choices' =>
                    [
                        '未返信' => 0,
                        '返信あり' => 1,
                    ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('contract_status', ChoiceType::class, [
                'choices' =>
                    [
                        '交渉中' => 0,
                        '成約 ' => 1,
                        '非成約' => 2,
                    ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('reason', IntegerType::class, [
                'required' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'conservation_contact';
    }
}
