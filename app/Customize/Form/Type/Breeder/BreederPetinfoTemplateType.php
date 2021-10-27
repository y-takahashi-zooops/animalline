<?php

namespace Customize\Form\Type\Breeder;

use Customize\Entity\BreederPetinfoTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BreederPetinfoTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('guarantee', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'ブリーダー様にて設定されている生体保障内容をご記入ください。',
                    'rows' => 10
                ],
                'required' => false
            ])
            ->add('delivery_way', TextareaType::class, [
                'attr' => [
                    'placeholder' => '引き渡し方法をご記入ください。',
                    'rows' => 10
                ],
                'required' => false
            ])
            ->add('payment_method', TextareaType::class, [
                'attr' => [
                    'placeholder' => '支払方法をご記入ください。',
                    'rows' => 10
                ],
                'required' => false,
            ])
            ->add('reservation_fee', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'ブリーダー様にて設定されている予約金をご記入ください。',
                    'rows' => 10
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederPetinfoTemplate::class,
        ]);
    }
}
