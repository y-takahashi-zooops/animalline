<?php

namespace Customize\Form\Type;

use Customize\Entity\ConservationPets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ConservationPetsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('breeder_id', IntegerType::class)
            ->add('pet_kind', IntegerType::class)
            ->add('breeds_type', IntegerType::class)
            ->add('pet_sex', IntegerType::class)
            ->add('pet_birthday',DateType::class)
            ->add('coat_color', IntegerType::class)
            ->add('future_wait', IntegerType::class)
            ->add('dna_check_result', IntegerType::class)
            ->add('pr_comment', TextType::class)
            ->add('description', TextType::class)
            ->add('delivery_time', TextType::class)
            ->add('delivery_way', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPets::class,
        ]);
    }
}
