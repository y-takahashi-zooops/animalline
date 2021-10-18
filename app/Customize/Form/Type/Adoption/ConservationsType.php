<?php

namespace Customize\Form\Type\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\Conservations;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\PrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationsType extends AbstractType
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
            ->add('is_organization', ChoiceType::class, [
                'choices' =>
                [
                    '個人' => AnilineConf::ANILINE_ORGANIZATION_PERSONAL,
                    '団体' => AnilineConf::ANILINE_ORGANIZATION_GROUP
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('organization_name', TextType::class, [
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
            ->add('handling_pet_kind', ChoiceType::class, [
                'choices' =>
                    [
                        '犬・猫' => AnilineConf::ANILINE_PET_KIND_DOG_CAT,
                        '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                        '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                    ],
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('owner_name', TextType::class, [
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
            ->add('owner_kana', TextType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[ァ-ヶｦ-ﾟー 　]+$/u',
                        'message' => 'form_error.kana_only',
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('pref_id', PrefType::class, [
                'attr' => ['class' => 'p-region-id'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('city', TextType::class, [
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
            ->add('address', TextType::class, [
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
            ->add('zip', TextType::class, [
                'trim' => true,
                'required' => false,
                'attr' => [
                    'maxlength' => 7,
                    'class' => 'p-postal-code',
                    'placeholder' => 'common.postal_code_sample'
                ],
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Length([
                        'max' => 7,
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('tel', TextType::class, [
                'trim' => true,
                'attr' => [
                    'maxlength' => 11,
                    'placeholder' => 'common.phone_number_sample'
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('fax', TextType::class, [
                'trim' => true,
                'required' => false,
                'attr' => [
                    'maxlength' => 11,
                    'placeholder' => 'common.phone_number_sample',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ])
                ]
            ])
            ->add('homepage_url', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^http/',
                        'message' => 'http://もしくはhttps://から入力してください。',
                    ]),
                ]
            ])
            ->add('pr_text', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('thumbnail_path', FileType::class, [
                // 'required' => false,
                'required' => true,
                'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conservations::class,
        ]);
    }
}
