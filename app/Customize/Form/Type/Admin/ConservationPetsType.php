<?php

namespace Customize\Form\Type\Admin;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPets;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationPetsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pet_kind', HiddenType::class, [
                'mapped' => false
            ])
            ->add('breeds_type', HiddenType::class, [
                'mapped' => false
            ])
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                    [
                        '男の子' => AnilineConf::ANILINE_PET_SEX_MALE,
                        '女の子' => AnilineConf::ANILINE_PET_SEX_FEMALE
                    ],
                'required' => true,
            ])
            ->add('pet_birthday', DateType::class, [
                'required' => true,
                'input' => 'datetime',
                'years' => range(1990, 2050),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
            ])
            ->add('coat_color', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 20,
                    ])
                ],
                'attr' => [
                    'maxlength' => 20,
                    'placeholder' => '毛色をご記入ください。'
                ],
            ])
            ->add('future_wait', TextType::class)
            ->add('dna_check_result', ChoiceType::class, [
                'choices' =>
                    [
                        '検査中' => AnilineConf::DNA_CHECK_RESULT_CHECKING,
                        '検査合格' => AnilineConf::DNA_CHECK_RESULT_CHECK_OK,
                        '検査NG' => AnilineConf::DNA_CHECK_RESULT_CHECK_NG
                    ],
                'required' => true,
            ])
            ->add('pr_comment', TextareaType::class)
            ->add('description', TextareaType::class)
            ->add('delivery_time', TextareaType::class)
            ->add('delivery_way', TextareaType::class)
            ->add('is_active', ChoiceType::class, [
                'choices' =>
                    [
                        '非公開' => AnilineConf::IS_ACTIVE_PRIVATE,
                        '公開' => AnilineConf::IS_ACTIVE_PUBLIC
                    ]
            ])
            ->add('release_date', DateType::class, [
                'required' => false,
                'input' => 'datetime',
                'years' => range(1990, 2050),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
            ])
            ->add('price', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPets::class,
        ]);
    }
}
