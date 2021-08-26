<?php

namespace Customize\Form\Type;

use Customize\Entity\BreederExaminationInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Customize\Config\AnilineConf;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class BreederExaminationInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pedigree_organization', RadioType::class, [
                'required' => true,
            ])
            ->add('breeding_pet_count', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_count_1', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_count_2', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_count_3', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_buy_place_1', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_buy_place_2', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_buy_place_3', IntegerType::class, [
                'required' => true,
            ])
            ->add('breeding_experience', ChoiceType::class, [
                'choices' =>
                [
                    'なし' => AnilineConf::EXPERIENCE_NONE,
                    '1～4回' => AnilineConf::EXPERIENCE_TO_FOUR,
                    '5～9回' => AnilineConf::EXPERIENCE_TO_NINE,
                    '10～19回' => AnilineConf::EXPERIENCE_TO_NINETEEN,
                    '20～49回' => AnilineConf::EXPERIENCE_TO_FORTYNINE,
                    '50回以上' => AnilineConf::EXPERIENCE_NONE,
                ],
                'attr' => [
                    'class' => 'ec-radio',
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('selling_experience', ChoiceType::class, [
                'choices' =>
                [
                    'なし' => AnilineConf::EXPERIENCE_NONE,
                    '1～4回' => AnilineConf::EXPERIENCE_TO_FOUR,
                    '5～9回' => AnilineConf::EXPERIENCE_TO_NINE,
                    '10～19回' => AnilineConf::EXPERIENCE_TO_NINETEEN,
                    '20～49回' => AnilineConf::EXPERIENCE_TO_FORTYNINE,
                    '50回以上' => AnilineConf::EXPERIENCE_NONE,
                ],
                'attr' => [
                    'class' => 'ec-radio',
                ],
                'expanded' => true,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederExaminationInfo::class,
        ]);
    }
}