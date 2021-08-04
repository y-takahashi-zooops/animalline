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
            ->add('user_id', IntegerType::class)
            ->add('adoption_house_name', TextType::class, [
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
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                    ]),
                ]
            ])
            ->add('adoption_house_address', TextType::class, [
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
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('is_active', IntegerType::class)
            ->add('examination_status', IntegerType::class)
            ->add('regist_reason', TextType::class)
            ->add('free_comment', TextType::class)
            ->add('can_publish_count', IntegerType::class)
            ->add('pet_exercise_env', IntegerType::class)
            ->add('cage_size', IntegerType::class)
            ->add('adoption_exp_year', IntegerType::class)
            ->add('staff_count_1', IntegerType::class)
            ->add('staff_count_2', IntegerType::class)
            ->add('staff_count_3', IntegerType::class)
            ->add('staff_count_4', IntegerType::class)
            ->add('email', EmailType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
                ],
            ])
            ->add('password', PasswordType::class, [
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
