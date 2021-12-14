<?php

namespace Customize\Form\Type\Front;

use Customize\Entity\BenefitsStatus;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\PrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class BenefitsStatusType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ContactType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('shipping_name', TextType::class, [
            'required' => true,
            'attr' => [
                'maxlength' => $this->eccubeConfig['eccube_stext_len'],
            ],
            'constraints' => [
                new Assert\Length([
                    'max' => $this->eccubeConfig['eccube_stext_len'],
                    'min' => 0
                ]),
                new Assert\NotBlank()
            ]
        ])
            ->add('shipping_zip', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Length([
                        'max' => 7,
                        'min' => 0
                    ]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'class' => 'p-postal-code',
                    'placeholder' => 'common.postal_code_sample',
                ],
                'trim' => true,
            ])
            ->add('pref', PrefType::class, [
                'attr' => ['class' => 'p-region-id'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('shipping_city', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_city_len'], 'min' => 0]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_city_len'],
                    'class' => 'p-locality',
                    'placeholder' => 'common.address_sample_01',
                ],
            ])
            ->add('shipping_address', TextType::class, [
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_address1_len'], 'min' => 0]),
                    new Assert\NotBlank()
                ],
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_address1_len'],
                    'class' => 'p-street-address p-extended-address',
                    'placeholder' => 'common.address_sample_02',
                ],
            ])
            ->add('shipping_tel', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 11,
                        'min' => 0
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BenefitsStatus::class,
        ]);
    }
}
