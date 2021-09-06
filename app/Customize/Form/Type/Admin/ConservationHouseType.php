<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\ConservationsHouse;
use Eccube\Common\EccubeConfig;
use Customize\Form\Type\ConservationAddressHouseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationHouseType extends AbstractType
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
            ->add('conservation_house_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                    'placeholder' => 'common.name',
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
            ->add('conservation_house_kana', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                    'placeholder' => 'common.kana',
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
            ->add('conservation_house_house_zip', TextType::class, [
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
            ->add('address', ConservationAddressHouseType::class)
            ->add('conservation_house_house_tel', TextType::class, [
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
            ->add('conservation_house_house_fax', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank(),
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 11,
                ],
                'trim' => true,
            ])
            ->add('conservation_house_front_name', TextType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                    'placeholder' => 'common.name',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\NotBlank(),
                ],
                'required' => true,
            ])
            ->add('conservation_house_front_tel', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[^\s ]+$/u',
                        'message' => 'form_error.not_contain_spaces',
                    ]),
                    new Assert\NotBlank(),
                ],
                'attr' => [
                    'placeholder' => 'common.phone_number_sample',
                    'maxlength' => 11,
                ],
                'trim' => true,
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationsHouse::class,
        ]);
    }
}
