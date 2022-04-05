<?php

namespace Customize\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TraningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('address', TextType::class, [
                'required' => false
            ])
            ->add('dog_breed', TextType::class, [
                'required' => false
            ])
            ->add('birthday', TextType::class, [
                'required' => false
            ])
            ->add('desired_date', DateType::class, [
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), 1990),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
            ])
            ->add('is_sensitive_sound', ChoiceType::class, [
                'choices' =>
                    [
                        '敏感' => 0,
                        '普通' => 1,
                        '鈍感' => 2
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_touch_body', ChoiceType::class, [
                'choices' =>
                    [
                        '敏感' => 0,
                        '特定の部位だけ' => 1,
                        '鈍感' => 2
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_interact_people', ChoiceType::class, [
                'choices' =>
                    [
                        '好き' => 0,
                        '普通' => 1,
                        '嫌い' => 2
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_change_attitude', ChoiceType::class, [
                'choices' =>
                    [
                        '変える' => 0,
                        '変えない' => 1
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('behavior_other_animals', ChoiceType::class, [
                'choices' =>
                    [
                        '気にする' => 0,
                        '普通' => 1,
                        '気にしない （気にするが過剰ではない）' => 2
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_wary', ChoiceType::class, [
                'choices' =>
                    [
                        '警戒をする' => 0,
                        '普通' => 1,
                        'しない' => 2,
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_attack', ChoiceType::class, [
                'choices' =>
                    [
                        'する' => 0,
                        'しない' => 1
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_like_food', ChoiceType::class, [
                'choices' =>
                    [
                        '好き' => 0,
                        '普通' => 1,
                        '好きではない' => 2
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_unfamiliar', ChoiceType::class, [
                'choices' =>
                    [
                        '慣れやすい' => 0,
                        '普通' => 1,
                        '慣れるまで時間がかかる' => 2
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('is_smell_or_approach', ChoiceType::class, [
                'choices' =>
                    [
                        'する' => 0,
                        'しない' => 1
                    ],
                'required' => false,
                'placeholder' => 'common.select'
            ]);
    }
    public function getBlockPrefix()
    {
        return 'ani_traning';
    }
}
