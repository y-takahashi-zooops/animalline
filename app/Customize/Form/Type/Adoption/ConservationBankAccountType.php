<?php

namespace Customize\Form\Type\Adoption;

use Customize\Entity\ConservationBankAccount;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationBankAccountType extends AbstractType
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
            ->add('bank_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 40,
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 0,
                        'max' => 40
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('bank_code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => "/^\d+$/u",
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\Length(['max' => 4]),
                ],
            ])
            ->add('branch_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 40,
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 0,
                        'max' => 40
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('branch_number', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[0-9]+$/u',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('account_number', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[0-9]+$/u',
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('account_kind', ChoiceType::class, [
                'choices' => [
                    '普通' => '1',
                    '貯蓄' => '2',
                    '当座' => '3'
                ],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 40,
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 0,
                        'max' => 40
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[ァ-ヶｦ-ﾟー 　]+$/u',
                        'message' => 'form_error.kana_only',
                    ]),
                    new Assert\NotBlank()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationBankAccount::class,
        ]);
    }

}
