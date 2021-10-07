<?php

namespace Customize\Form\Type;

use Customize\Config\AnilineConf;
use Customize\Entity\Breeders;
use Eccube\Common\EccubeConfig;
use Customize\Form\Type\BreederAddressType;
use Customize\Form\Type\LicenseAddressType;
use Eccube\Form\Type\RepeatedEmailType;
use Eccube\Form\Validator\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdminBreederType extends AbstractType
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
                    new Assert\Regex([
                        'pattern' => '/^[^\s ]+$/u',
                        'message' => 'form_error.not_contain_spaces',
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
                    new Assert\Regex([
                        'pattern' => '/^[ァ-ヶｦ-ﾟー]+$/u',
                        'message' => 'form_error.kana_only',
                    ]),
                    new Assert\NotBlank()
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
            ->add('addr', BreederAddressType::class)
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
                'required' => false,
            ])
            ->add('regal_effort', TextareaType::class, [
                'required' => false,
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
                    new Assert\Regex([
                        'pattern' => '/^[^\s ]+$/u',
                        'message' => 'form_error.not_contain_spaces',
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
            ->add('license_addr', LicenseAddressType::class)
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
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), 1990),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
            ])
            ->add('license_expire_date', DateType::class, [
                'required' => false,
                'input' => 'datetime',
                'years' => range(2050, date('Y')),
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
                    ])
                ],
            ])
            ->add('license_type', ChoiceType::class, [
                'choices' =>
                    [
                        '販売業' => AnilineConf::ANILINE_LICENSE_SALES,
                        '保管業' => AnilineConf::ANILINE_LICENSE_CUSTODY,
                        '貸出業' => AnilineConf::ANILINE_LICENSE_LENDING,
                        '訓練業' => AnilineConf::ANILINE_LICENSE_TRAINING,
                        '展示業' => AnilineConf::ANILINE_LICENSE_EXHIBITION,
                        'その他' => AnilineConf::ANILINE_LICENSE_OTHER
                    ],
                'required' => false,
                'placeholder' => 'common.select'
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Breeders::class,
        ]);
    }
}
