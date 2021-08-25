<?php

namespace Customize\Form\Type;

use Customize\Entity\ConservationPetImage;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationPetImageType extends AbstractType
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
            ->add('ConservationPet', EntityType::class, [
                'class' => 'Customize\Entity\ConservationPets',
                'choice_label' => function (\Customize\Entity\ConservationPets $pet) {
                    return $pet->getId();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('conservation_pet_id', IntegerType::class)
            ->add('image_type', IntegerType::class)
            ->add('image_uri', TextType::class, [
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    'attr' => [
                        'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                    ],
                ]
            ])
            ->add('sort_order', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPetImage::class,
        ]);
    }
}
