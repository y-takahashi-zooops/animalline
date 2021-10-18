<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                    '検査中' => AnilineConf::DNA_CHECK_RESULT_CHECKING,
                    '検査合格' => AnilineConf::DNA_CHECK_RESULT_CHECK_OK,
                    '検査NG' => AnilineConf::DNA_CHECK_RESULT_CHECK_NG
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
                'expanded' => true
            ])
            ->add('Pedigree', EntityType::class, [
                'class' => 'Customize\Entity\Pedigree',
                'choice_label' => function (\Customize\Entity\Pedigree $petdigree) {
                    return $petdigree->getPedigreeName();
                },
                'required' => false,
            ])
            ->add('pedigree_code', IntegerType::class, [
                'required' => false,
            ])
            ->add('microchip_code', IntegerType::class, [
                'required' => false,
            ])
            ->add('include_vaccine_fee', ChoiceType::class, [
                'choices' => [
                    '有' => AnilineConf::CAN_BE,
                    '無し' => AnilineConf::NONE
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => true
            ])
            ->add('delivery_way', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5
                ]
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
