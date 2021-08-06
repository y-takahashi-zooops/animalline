<?php

namespace Customize\Form\Type;

use Customize\Entity\Conservations;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Form\Validator\Email;

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
            ->add('user_id', IntegerType::class, [
                'required' => false,
            ])
            ->add('adoption_house_name', TextType::class, [
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
            ->add('adoption_house_zip', TextType::class, [
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
            ->add('adoption_house_pref', TextType::class, [
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
            ->add('adoption_house_city', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                    ]),
                ]
            ])
            ->add('adoption_house_address', TextType::class, [
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
            ->add('adoption_house_building', TextType::class, [
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
            ->add('adoption_house_tel', TextType::class, [
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
            ->add('adoption_house_fax', TextType::class, [
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
            ->add('is_active', IntegerType::class, [
                'required' => false,
            ])
            ->add('examination_status', IntegerType::class, [
                'required' => false,
            ])
            ->add('regist_reason', TextType::class, [
                'required' => false,
            ])
            ->add('free_comment', TextType::class, [
                'required' => false,
            ])
            ->add('can_publish_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('pet_exercise_env', IntegerType::class, [
                'required' => false,
            ])
            ->add('cage_size', IntegerType::class, [
                'required' => false,
            ])
            ->add('adoption_exp_year', IntegerType::class, [
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
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
                ],
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
            ->add('salt', TextType::class, [
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
            ->add('secret_key', TextType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conservations::class,
        ]);
    }
}
