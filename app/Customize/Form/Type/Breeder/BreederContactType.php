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

use Customize\Config\AnilineConf;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BreederContactType extends AbstractType
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
            ->add('contact_type', ChoiceType::class, [
                'choices' =>
                    [
                        '問い合わせ' => AnilineConf::CONTACT_TYPE_INQUIRY,
                        '見学希望' => AnilineConf::CONTACT_TYPE_VISIT_REQUEST,
                        '返信' => AnilineConf::CONTACT_TYPE_REPLY,
                    ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('contact_description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr' => [
                    'placeholder' => '○○県から、家族○人で見学に伺うことは可能でしょうか。
子犬の動画や、親犬の写真を見せていただくことは可能でしょうか。',
                ],
            ])
            ->add('booking_request', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => '第一候補日：○月○日○時～
第二候補日：▲月▲日▲時以降

土日の日中を希望します。',
                ],
            ])
            ->add('files', FileType::class, [
                'label' => '添付ファイル',
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2m',
                    ]),
                    new \Symfony\Component\Validator\Constraints\File([
                        'mimeTypes' => [
                            'image/*',
                        ]
                    ])
                ],
                'mapped' => false,
                'data_class' => null
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'breeder_contact';
    }
}
