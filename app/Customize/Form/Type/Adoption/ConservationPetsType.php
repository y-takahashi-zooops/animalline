<?php

namespace Customize\Form\Type\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\Breeds;
use Customize\Entity\ConservationPets;
use Customize\Repository\ConservationPetsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

class ConservationPetsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pet_kind', ChoiceType::class, [
                'choices' =>
                [
                    '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                    '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                ],
                'required' => true,
            ])
            ->add('BreedsType', EntityType::class, [
                'class' => 'Customize\Entity\Breeds',
                'choice_label' => function (Breeds $breeds) {
                    return $breeds->getBreedsName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => 'common.select'
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 64,
                    ])
                ],
                'attr' => [
                    'maxlength' => 64,
                ],
            ])
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                [
                    '男の子' => AnilineConf::ANILINE_PET_SEX_MALE,
                    '女の子' => AnilineConf::ANILINE_PET_SEX_FEMALE
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => '----'
            ])
            ->add('pet_age', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 10,
                        'min' => 0,
                    ])
                ],
                'attr' => [
                    'maxlength' => 10,
                ],
            ])
            ->add('coat_color', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 20,
                        'min' => 0,
                    ])
                ],
                'attr' => [
                    'maxlength' => 20,
                    'placeholder' => '毛色をご記入ください。'
                ],
            ])
            ->add('pr_comment', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('delivery_time', TextareaType::class, [
                'required' => false,
            ])
            ->add('delivery_way', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('thumbnail_path', FileType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img1'
                ],
                'data_class' => null
            ])
            ->add('image1', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img2'
                ],
                'data_class' => null
            ])
            ->add('image2', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img3'
                ],
                'data_class' => null
            ])
            ->add('image3', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img4'
                ],
                'data_class' => null
            ])
            ->add('image4', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img5'
                ],
                'data_class' => null
            ])
            ->add('is_vaccine', ChoiceType::class, [
                'choices' =>
                [
                    '未接種' => 0,
                    '接種済' => 1
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => '----'

            ])
            ->add('is_castration', ChoiceType::class, [
                'choices' =>
                [
                    '未手術' => 0,
                    '手術済' => 1
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => '----'
            ])
            ->add('is_single', ChoiceType::class, [
                'choices' =>
                [
                    '応募不可' => 0,
                    '応募可' => 1
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => '----'
            ])
            ->add('is_senior', ChoiceType::class, [
                'choices' =>
                [
                    '応募不可' => 0,
                    '応募可' => 1
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => '----'
            ])
            ->add('ImagePathErrors', TextType::class, [
                'mapped' => false,
            ]);;
        /*
        ->add('is_active', ChoiceType::class, [
            'choices' =>
            [
                '非公開' => AnilineConf::IS_ACTIVE_PRIVATE,
                '公開' => AnilineConf::IS_ACTIVE_PUBLIC
            ]
        ])
        ->add('release_date', DateType::class)
        ->add('price', IntegerType::class);
        */
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateThumbnail']);
    }

    public function validateThumbnail(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$event->getForm()->getConfig()->getOptions()['image1']) {
            $form['ImagePathErrors']->addError(new FormError('写真を1点以上アップロードください。'));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPets::class,
            'customer' => null,
            'image1' => null
        ]);
    }
}
