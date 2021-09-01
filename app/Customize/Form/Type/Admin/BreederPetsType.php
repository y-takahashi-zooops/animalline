<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Customize\Entity\BreederPets;
use Customize\Config\AnilineConf;

class BreederPetsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pet_kind', HiddenType::class)
            ->add('breeds_type', HiddenType::class, [
                'mapped' => false
            ])
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                [
                    '男の子' => AnilineConf::ANILINE_PET_SEX_MALE,
                    '女の子' => AnilineConf::ANILINE_PET_SEX_FEMALE
                ]
            ])
            ->add('pet_birthday', DateType::class)
            ->add('coat_color', HiddenType::class, [
                'mapped' => false
            ])
            ->add('future_wait', IntegerType::class)
            ->add('dna_check_result', ChoiceType::class, [
                'choices' =>
                [
                    '結果①' => AnilineConf::DNA_CHECK_RESULT_1,
                    '結果②' => AnilineConf::DNA_CHECK_RESULT_2
                ]
            ])
            ->add('pr_comment', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
            ])
            ->add('is_breeding', ChoiceType::class, [
                'choices' => [
                    '可' => AnilineConf::CAN_BE,
                    '不可' => AnilineConf::NONE
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => true,
            ])
            ->add('is_selling', ChoiceType::class, [
                'choices' => [
                    '可' => AnilineConf::CAN_BE,
                    '不可' => AnilineConf::NONE
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => true,
            ])
            ->add('guarantee', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
            ])
            ->add('is_pedigree', ChoiceType::class, [
                'choices' => [
                    '有' => AnilineConf::CAN_BE,
                    '無し' => AnilineConf::NONE
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => true,
            ])
            ->add('include_vaccine_fee', ChoiceType::class, [
                'choices' => [
                    '有' => AnilineConf::CAN_BE,
                    '無し' => AnilineConf::NONE
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => true,
            ])
            ->add('delivery_time', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
            ])
            ->add('delivery_way', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
            ])
            ->add('payment_method', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
            ])
            ->add('reservation_fee', IntegerType::class, [
                'required' => false
            ])
            ->add('price', IntegerType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederPets::class,
        ]);
    }
}
