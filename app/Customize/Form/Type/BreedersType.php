<?php

namespace Customize\Form\Type;

use Customize\Config\AnilineConf;
use Customize\Entity\Breeders;
use Eccube\Common\EccubeConfig;
use Customize\Form\Type\AddressType;
use Customize\Form\Type\LicenseAddressType;
use Customize\Form\Type\KanaType;
use Customize\Form\Type\NameType;
use Eccube\Form\Type\PhoneNumberType;
use Eccube\Form\Type\PostalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Form\Validator\Email;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

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
            ->add('breeder_name', NameType::class, [
                'required' => true,/*
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]*/
            ])
            ->add('breeder_kana', KanaType::class, []/*, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ]*/)
            ->add('breeder_zip', PostalType::class)
            ->add('addr', AddressType::class)
            ->add('breeder_tel', PhoneNumberType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 10,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                    ]),
                ]
            ])
            ->add('breeder_fax', PhoneNumberType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 10,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                    ]),
                ]
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
                ]
            ])
            ->add('license_zip', PostalType::class)
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
                ]
            ])
            ->add('license_regist_date', BirthdayType::class, [
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), date('Y') - $this->eccubeConfig['eccube_birth_max']),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'constraints' => [
                    new Assert\LessThanOrEqual([
                        'value' => date('Y-m-d', strtotime('-1 day')),
                        'message' => 'form_error.select_is_future_or_now_date',
                    ]),
                ],
            ])
            ->add('license_expire_date', BirthdayType::class, [
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), date('Y') - $this->eccubeConfig['eccube_birth_max']),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'constraints' => [
                    new Assert\LessThanOrEqual([
                        'value' => date('Y-m-d', strtotime('-1 day')),
                        'message' => 'form_error.select_is_future_or_now_date',
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
