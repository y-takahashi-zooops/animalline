<?php

namespace Customize\Form\Type;

use Customize\Entity\ConservationPets;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationPetsType extends AbstractType
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
            ->add('breeder_id', IntegerType::class)
            ->add('pet_kind', IntegerType::class)
            ->add('breeds_type', IntegerType::class)
            ->add('pet_sex', IntegerType::class)
            ->add('pet_birthday', DateType::class)
            ->add('coat_color', IntegerType::class)
            ->add('future_wait', IntegerType::class)
            ->add('dna_check_result', IntegerType::class)
            ->add('pr_comment', TextType::class)
            ->add('description', TextType::class)
            ->add('delivery_time', TextType::class)
            ->add('delivery_way', TextType::class)
            ->add('thumbnail_path', TextType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('release_status', IntegerType::class)
            ->add('release_date', DateType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPets::class,
        ]);
    }
}
