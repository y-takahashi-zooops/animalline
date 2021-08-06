<?php

namespace Customize\Form\Type;

use Customize\Entity\Breeders;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ->add('breeder_house_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('owner_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('owner_kana', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('breeder_house_tel', TextType::class, [
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
            ->add('breeder_house_fax', TextType::class, [
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
            ->add('breeder_house_zip', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 7,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 7,
                    ]),
                ]
            ])
            ->add('breeder_house_pref', TextType::class, [
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
            ->add('breeder_house_city', TextType::class, [
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
            ->add('breeder_house_address', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('breeder_house_building', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('responsible_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('responsible_kana', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('responsible_zip', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 7,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 7,
                    ]),
                ]
            ])
            ->add('responsible_pref', TextType::class, [
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
            ->add('responsible_city', TextType::class, [
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
            ->add('responsible_address', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('office_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('authorization_type', IntegerType::class, [
                'required' => false,
            ])
            ->add('pet_parent_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('staff_count_1', IntegerType::class, [
                'required' => false,
            ])
            ->add('staff_count_2', IntegerType::class, [
                'required' => false,
            ])
            ->add('staff_count_3', IntegerType::class, [
                'required' => false,
            ])
            ->add('staff_count_4', IntegerType::class, [
                'required' => false,
            ])
            ->add('breed_exp_year', IntegerType::class, [
                'required' => false,
            ])
            ->add('is_participation_show', IntegerType::class, [
                'required' => false,
            ])
            ->add('cage_size', IntegerType::class, [
                'required' => false,
            ])
            ->add('pet_exercise_env', IntegerType::class, [
                'required' => false,
            ])
            ->add('can_publish_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('self_breed_exp_year', IntegerType::class, [
                'required' => false,
            ])
            ->add('direct_sell_exp', IntegerType::class, [
                'required' => false,
            ])
            ->add('is_pet_trade', IntegerType::class, [
                'required' => false,
            ])
            ->add('sell_route', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('is_full_time', IntegerType::class, [
                'required' => false,
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
                ]
            ])
            ->add('sns_url', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('regist_reason', TextType::class, [
                'required' => false,
            ])
            ->add('free_comment', TextType::class, [
                'required' => false,
            ])
            ->add('introducer_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('examination_status', IntegerType::class, [
                'required' => false,
            ])
            ->add('is_active', IntegerType::class, [
                'required' => false,
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Breeders::class,
        ]);
    }
}
