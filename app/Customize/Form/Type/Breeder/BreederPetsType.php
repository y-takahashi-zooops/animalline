<?php

namespace Customize\Form\Type\Breeder;

use Customize\Entity\BreederPets;
use Customize\Entity\Breeds;
use Customize\Entity\CoatColors;
use Customize\Entity\Pedigree;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints as Assert;
use Customize\Config\AnilineConf;
use Customize\Repository\BreedersRepository;
use Symfony\Component\Security\Core\User\User;

class BreederPetsType extends AbstractType
{

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    public function __construct(BreedersRepository $breedersRepository)
    {
        $this->breedersRepository = $breedersRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('breeds_type', EntityType::class, [
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
            ->add('pet_birthday', DateType::class, [
                'data' => new DateTime(),
                'years' => range(date('Y'), 1990),
            ])
            ->add('band_color', ChoiceType::class, [
                'choices' =>
                [
                    '赤' => AnilineConf::ANILINE_BAND_COLOR_RED,
                    '青' => AnilineConf::ANILINE_BAND_COLOR_BLUE,
                    '緑' => AnilineConf::ANILINE_BAND_COLOR_GREEN,
                    '黄色' => AnilineConf::ANILINE_BAND_COLOR_YELLOW,
                    'ピンク' => AnilineConf::ANILINE_BAND_COLOR_PINK,
                    'オレンジ' => AnilineConf::ANILINE_BAND_COLOR_ORANGE
                ],
                'required' => false,
                'placeholder' => 'common.select'
            ])
            ->add('coat_color', EntityType::class, [
                'class' => 'Customize\Entity\CoatColors',
                'choice_label' => function (CoatColors $coatColors) {
                    return $coatColors->getCoatColorName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('future_wait', IntegerType::class, [
                'required' => false,
            ])
            //->add('dna_check_result', IntegerType::class)
            ->add('pr_comment', TextType::class, [
                'attr' => [
                    'maxlength' => 64,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 64,
                    ]),
                ],
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'attr' => [
                    'maxlength' => 64,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('guarantee', TextareaType::class)
            ->add('is_pedigree', ChoiceType::class, [
                'choices'  => [
                    'あり'   => '1',
                    'なし' => '0',
                ],
                'expanded' => true,
            ])
            ->add('Pedigree', EntityType::class, [
                'class' => 'Customize\Entity\Pedigree',
                'choice_label' => function (Pedigree $petdigree) {
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
                'choices'  => [
                    'あり'   => '1',
                    'なし' => '0',
                ],
                'expanded' => true,
            ])
            ->add('delivery_way', TextareaType::class)
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
            ->add('price', IntegerType::class);

        $customer = $options['customer'];
        $breeder = $this->breedersRepository->find($customer);
        $handling_pet_kind = $breeder->getHandlingPetKind();
        if ($handling_pet_kind == 0) {
            $choices = [
                    '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                    '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                ];
        } elseif ($handling_pet_kind == 1) {
            $choices = ['犬' => AnilineConf::ANILINE_PET_KIND_DOG,];
        } elseif ($handling_pet_kind == 2) {
            $choices = ['猫' => AnilineConf::ANILINE_PET_KIND_CAT];
        }

        $builder
            ->add('pet_kind', ChoiceType::class, [
                'choices' => $choices,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederPets::class,
            'customer' => null
        ]);
    }
}
