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
            ])
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                    [
                        '男の子' => AnilineConf::ANILINE_PET_SEX_MALE,
                        '女の子' => AnilineConf::ANILINE_PET_SEX_FEMALE
                    ],
                'required' => true,
            ])
            ->add('pet_birthday', DateType::class)
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
            ->add('future_wait', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            //->add('dna_check_result', IntegerType::class)
            ->add('pr_comment', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('delivery_time', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('delivery_way', TextareaType::class, [
                'required' => true,
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
            ]);
        /*
        ->add('is_active', ChoiceType::class, [
            'choices' =>
            [
                '非公開' => AnilineConf::RELEASE_STATUS_PRIVATE,
                '公開' => AnilineConf::RELEASE_STATUS_PUBLIC
            ]
        ])
        ->add('release_date', DateType::class)
        ->add('price', IntegerType::class);
        */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPets::class,
            'customer' => null
        ]);
    }
}
