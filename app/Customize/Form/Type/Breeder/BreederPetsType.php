<?php

namespace Customize\Form\Type\Breeder;

use Customize\Entity\BreederPets;
use Customize\Entity\Breeds;
use Customize\Entity\Pedigree;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'placeholder' => 'common.select'
            ])
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                [
                    '男の子(オス)' => AnilineConf::ANILINE_PET_SEX_MALE,
                    '女の子(メス)' => AnilineConf::ANILINE_PET_SEX_FEMALE
                ],
                'required' => true,
            ])
            ->add('pet_birthday', DateType::class, [
                'data' => new DateTime(),
                'years' => range(date('Y'), 2000),
                'required' => true,
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
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank(),
                ]
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
            ->add('future_wait', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan([
                        'value' => 0,
                    ]),
                ]
            ])
            //->add('dna_check_result', IntegerType::class)
            ->add('pr_comment', TextType::class, [
                'attr' => [
                    'maxlength' => 25,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 25,
                    ]),
                    new Assert\NotBlank(),
                ],
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('guarantee', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('is_pedigree', ChoiceType::class, [
                'choices'  => [
                    'あり'   => '1',
                    'なし' => '0',
                ],
                'expanded' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('vaccine_detail', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'フリー記入',
                ],
            ])
            ->add('Pedigree', EntityType::class, [
                'class' => 'Customize\Entity\Pedigree',
                'choice_label' => function (Pedigree $pedigree) {
                    return $pedigree->getPedigreeName();
                },
                'required' => false,
            ])
            ->add('pedigree_code', IntegerType::class, [
                'required' => false,
            ])
            /*
            ->add('microchip_code', IntegerType::class, [
                'required' => false,
            ])
            */
            ->add('include_vaccine_fee', ChoiceType::class, [
                'choices'  => [
                    '代金に含む'   => '1',
                    '代金に含まない' => '0',
                ],
                'expanded' => true,
            ])
            ->add('delivery_way', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('payment_method', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('reservation_fee', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
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
            ->add('price', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('vaccineDetailErrors', TextType::class, [
                'mapped' => false
            ])
            ->add('pedigreeErrors', TextType::class, [
                'mapped' => false
            ])
            ->add('pedigreeCodeErrors', TextType::class, [
                'mapped' => false
            ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateVaccineDetail']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validatePedigree']);

        $customer = $options['customer'];
        $breeder = $this->breedersRepository->find($customer);
        $handling_pet_kind = $breeder->getHandlingPetKind();
        $choices = [];
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

    public function validateVaccineDetail(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        if ($data->getIncludeVaccineFee() && !$data->getVaccineDetail()) {
            $form['vaccineDetailErrors']->addError(new FormError('入力されていません。'));
        }
    }

    public function validatePedigree(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        if ($data->getIsPedigree()) {
            if (!$data->getPedigree()) {
                $form['pedigreeErrors']->addError(new FormError('入力されていません。'));
            }
            if (!$data->getPedigreeCode()) {
                $form['pedigreeCodeErrors']->addError(new FormError('入力されていません。'));
            }
        }
    }
}
