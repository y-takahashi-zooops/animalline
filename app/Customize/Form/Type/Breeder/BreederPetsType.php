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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
use Customize\Repository\BreedsRepository;
use Symfony\Component\Security\Core\User\User;
use Customize\Form\DataTransformer\IntegerToBooleanTransformer;

class BreederPetsType extends AbstractType
{

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;
    
    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    public function __construct(
        BreedersRepository $breedersRepository,
        BreedsRepository $breedsRepository
    )
    {
        $this->breedersRepository = $breedersRepository;
        $this->breedsRepository = $breedsRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                [
                    '男の子(オス)' => AnilineConf::ANILINE_PET_SEX_MALE,
                    '女の子(メス)' => AnilineConf::ANILINE_PET_SEX_FEMALE
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => '----'
            ])
            ->add('pet_birthday', DateType::class, [
                // 'data' => new DateTime(),
                'years' => range(date('Y'), 2010),
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
                'required' => false,
                'placeholder' => 'common.select'
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
            ->add('is_microchip', ChoiceType::class, [
                'choices'  => [
                    '代金に含む'   => '1',
                    '代金に含まない' => '0',
                ],
                'expanded' => true,
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
            ->add('pedigree_detail', TextType::class, [
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
            /*
            ->add('pedigree_code', IntegerType::class, [
                'required' => false,
            ])
            */
            ->add('microchip_code', TextType::class, [
                'required' => false,
            ])
            ->add('include_vaccine_fee', ChoiceType::class, [
                'choices'  => [
                    '代金に含む'   => '1',
                    '代金に含まない' => '0',
                ],
                'expanded' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ]
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
            ->add('price_no_display', CheckboxType::class, [
                'label' => 'サイト上に金額を表示しない',
                'required' => false,
            ])
            ->add('vaccineDetailErrors', TextType::class, [
                'mapped' => false
            ])
            ->add('pedigreeErrors', TextType::class, [
                'mapped' => false
            ])
            ->add('pedigreeCodeErrors', TextType::class, [
                'mapped' => false
            ])
            ->add('ImagePathErrors', TextType::class, [
                'mapped' => false,
            ]);

        $builder->get('price_no_display')->addModelTransformer(new IntegerToBooleanTransformer());

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateVaccineDetail']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validatePedigree']);

        $customer = $options['customer'];
        $handling_pet_kind = $options['pet_kind'];

        $breeder = $this->breedersRepository->find($customer);
        //$handling_pet_kind = $breeder->getHandlingPetKind();
        $choices = [];
        if ($handling_pet_kind == 0) {
            $choices = [
                    '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                    '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                ];
            
            $br = $this->breedsRepository->createQueryBuilder('b')
                    ->orderBy('b.sort_order', 'ASC');
        } elseif ($handling_pet_kind == 1) {
            $choices = ['犬' => AnilineConf::ANILINE_PET_KIND_DOG,];

            $br = $this->breedsRepository->createQueryBuilder('b')
                    ->where('b.pet_kind = 1')
                    ->orderBy('b.sort_order', 'ASC');
        } elseif ($handling_pet_kind == 2) {
            $choices = ['猫' => AnilineConf::ANILINE_PET_KIND_CAT];

            $br = $this->breedsRepository->createQueryBuilder('b')
                    ->where('b.pet_kind = 2')
                    ->orderBy('b.sort_order', 'ASC');
        }

        $builder
            ->add('pet_kind', ChoiceType::class, [
                'choices' => $choices,
                'required' => true,
            ])
            ->add('breeds_type', EntityType::class, [
                'class' => 'Customize\Entity\Breeds',
                'query_builder' => $br,
                'choice_label' => function (Breeds $breeds) {
                    return $breeds->getBreedsName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'placeholder' => 'common.select'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederPets::class,
            'customer' => null,
            'pet_kind' => null
        ]);
    }

    public function validateVaccineDetail(FormEvent $event)
    {
        /*
        $data = $event->getData();
        $form = $event->getForm();
        if ($data->getIncludeVaccineFee() && !$data->getVaccineDetail()) {
            $form['vaccineDetailErrors']->addError(new FormError('入力されていません。'));
        }
        */
    }

    public function validatePedigree(FormEvent $event)
    {
        /*
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
        */
    }
}
