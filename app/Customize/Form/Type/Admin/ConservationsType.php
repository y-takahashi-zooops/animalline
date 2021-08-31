<?php

namespace Customize\Form\Type\Admin;

use Customize\Config\AnilineConf;
use Customize\Entity\Conservations;
use Customize\Form\Type\Adoption\ConservationAddressType;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Validator\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('organization_name', TextType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ])
                ]
            ])
            ->add('handling_pet_kind', ChoiceType::class, [
                'choices' =>
                [
                    '犬・猫' => AnilineConf::ANILINE_PET_KIND_DOG_CAT,
                    '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                    '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                ]
            ])
            ->add('thumbnail_path', HiddenType::class, [
                'required' => false
            ])
            ->add('owner_name', TextType::class, [
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
            ->add('owner_kana', TextType::class, [
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
            ->add('addr', ConservationAddressType::class)
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
                    ])
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
                ]
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
                    '審査中' => AnilineConf::EXAMINATION_STATUS_UNDER,
                    '審査OK' => AnilineConf::EXAMINATION_STATUS_OK,
                    '審査NG' => AnilineConf::EXAMINATION_STATUS_NG
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
            ->add('email', EmailType::class, [
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
            'data_class' => Conservations::class,
        ]);
    }
}
