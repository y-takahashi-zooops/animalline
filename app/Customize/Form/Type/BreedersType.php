<?php

namespace Customize\Form\Type;

use Customize\Config\AnilineConf;
use Customize\Entity\Breeders;
use Eccube\Common\EccubeConfig;
use Customize\Form\Type\AddressType;
use Customize\Form\Type\LicenseAddressType;
use Eccube\Form\Type\PhoneNumberType;
use Eccube\Form\Type\PostalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('addr', AddressType::class)
            ->add('breeder_tel', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 10,
                ],
                'trim' => true,
            ])
            ->add('breeder_fax', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ])
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 10,
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
                'years' => range(date('Y'), 1990),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'constraints' => [
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[license_regist_date].data'
                    ]),
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Breeders::class,
        ]);
    }
}
