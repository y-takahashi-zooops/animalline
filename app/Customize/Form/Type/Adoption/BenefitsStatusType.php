<?php

namespace Customize\Form\Type\Adoption;

use Customize\Entity\BenefitsStatus;
use Eccube\Form\Type\AddressType;
use Eccube\Form\Type\PostalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BenefitsStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipping_name', NameType::class, [
                'required' => true,
            ])
            ->add('postal_code', PostalType::class)
            ->add('address', AddressType::class)
            ->add('shipping_tel', PhoneNumberType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BenefitsStatus::class,
        ]);
    }
}
