<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\BreederHouse;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\IntegerType;
use Eccube\Common\EccubeConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterProductsType extends AbstractType
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
        $builder->add('order_date', DateType::class)
            ->add('supplier_code', EntityType::class, [
                'class' => 'Customize\Entity\Supplier',
                'choice_label' => function (\Customize\Entity\Supplier $supplier) {
                    return $supplier->getSupplierName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('arrival_date_schedule', DateType::class)
            ->add('remark_text', TextareaType::class)
            ->add('purchase_price', IntegerType::class)
            ->add('arrival_quantity', IntegerType::class)
            ->add('arrival_box_schedule', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederHouse::class,
        ]);
    }
}
