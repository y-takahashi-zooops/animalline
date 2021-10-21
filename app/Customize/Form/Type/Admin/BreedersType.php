<?php

namespace Customize\Form\Type\Admin;

use Customize\Config\AnilineConf;
use Customize\Entity\Breeders;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\PrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BreedersType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('breeder_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('breeder_kana', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[ァ-ヶｦ-ﾟー 　]+$/u',
                        'message' => 'form_error.kana_only',
                    ]),
                ]
            ])
            ->add('breeder_zip', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Length([
                        'max' => 7,
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'class' => 'p-postal-code',
                    'placeholder' => 'common.postal_code_sample',
                ],
                'trim' => true,
            ])
            ->add('PrefBreeder', PrefType::class, [
                'attr' => ['class' => 'p-region-id'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('breeder_city', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_city_len']]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_city_len'],
                    'class' => 'p-locality',
                    'placeholder' => 'common.address_sample_01',
                ],
            ])
            ->add('breeder_address', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_address1_len']]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_address1_len'],
                    'class' => 'p-street-address p-extended-address',
                    'placeholder' => 'common.address_sample_02',
                ],
            ])
            ->add('breeder_tel', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 11,
                ],
                'trim' => true,
            ])
            ->add('breeder_fax', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ])
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 11,
                ],
                'trim' => true,
            ])
            ->add('pr_text', TextareaType::class, [
                // 'required' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('regal_effort', TextareaType::class, [
                // 'required' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_no', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_zip', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Length([
                        'max' => 7,
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'class' => 'p-postal-code',
                    'placeholder' => 'common.postal_code_sample',
                ],
                'trim' => true,
            ])
            ->add('PrefLicense', PrefType::class, [
                'attr' => ['class' => 'p-region-id'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_city', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_city_len']]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_city_len'],
                    'class' => 'p-locality',
                    'placeholder' => 'common.address_sample_01',
                ],
            ])
            ->add('license_address', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_address1_len']]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_address1_len'],
                    'class' => 'p-street-address p-extended-address',
                    'placeholder' => 'common.address_sample_02',
                ],
            ])
            ->add('license_house_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_house_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[^\s ]+$/u',
                        'message' => 'form_error.not_contain_spaces',
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_manager_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[^\s ]+$/u',
                        'message' => 'form_error.not_contain_spaces',
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_regist_date', DateType::class, [
                'required' => true,
                'input' => 'datetime',
                'years' => range(date('Y'), 1990),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('license_expire_date', DateType::class, [
                'required' => true,
                'input' => 'datetime',
                'years' => range(date('Y'), 2050),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'constraints' => [
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[license_regist_date].data',
                        'message' => '有効期限の末日は登録年月日より大きくなければなりません。'
                    ]),
                    new Assert\GreaterThan([
                        'value' => date('Y-m-d'),
                        'message' => '有効期限の末日は未来日となければなりません。'
                    ]),
                    new Assert\NotBlank()
                ],
            ])
            ->add('thumbnail_path', FileType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('handling_pet_kind', ChoiceType::class, [
                'choices' =>
                    [
                        '犬・猫' => AnilineConf::ANILINE_PET_KIND_DOG_CAT,
                        '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                        '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                    ],
                'required' => true,
            ])
            ->add('is_active', ChoiceType::class, [
                'choices' =>
                    [
                        '公開' => AnilineConf::PUBLIC_FLAG_RELEASE,
                        '非公開' => AnilineConf::PUBLIC_FLAG_PRIVATE,
                    ],
            ])
            ->add('examination_status', ChoiceType::class, [
                'choices' =>
                    [
                        '審査中' => AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK,
                        '審査OK' => AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK,
                        '審査NG' => AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG
                    ]
            ])
            // ->add('email', EmailType::class, [
            //     'attr' => [
            //         'maxlength' => $this->eccubeConfig['eccube_stext_len'],
            //     ],
            //     'constraints' => [
            //         new Assert\NotBlank(),
            //         new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
            //     ],
            // ])
            ->add('thumbnail_path', HiddenType::class, [
                'required' => false
            ])
            ->add('license_thumbnail_path', HiddenType::class, [
                'required' => false
            ])
            ->add('breeder_house_name_dog', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('breeder_house_name_cat', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('DogHouseNameErrors', TextType::class, [
                'mapped' => false,
            ])
            ->add('CatHouseNameErrors', TextType::class, [
                'mapped' => false,
            ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validatePetHouseName']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Breeders::class,
        ]);
    }

    public function validatePetHouseName(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        if (in_array($data->getHandlingPetKind(), [AnilineConf::ANILINE_PET_KIND_DOG_CAT, AnilineConf::ANILINE_PET_KIND_DOG]) && !$data->getBreederHouseNameDog()) {
            $form['DogHouseNameErrors']->addError(new FormError('入力されていません。'));
        }
        if (in_array($data->getHandlingPetKind(), [AnilineConf::ANILINE_PET_KIND_DOG_CAT, AnilineConf::ANILINE_PET_KIND_CAT]) && !$data->getBreederHouseNameCat()) {
            $form['CatHouseNameErrors']->addError(new FormError('入力されていません。'));
        }
    }
}
