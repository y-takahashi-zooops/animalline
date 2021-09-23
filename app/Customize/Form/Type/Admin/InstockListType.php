<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\InstockScheduleHeader;
use Customize\Repository\SupplierRepository;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstockListType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var SupplierRepository
     */
    protected $supplierRepository;

    public function __construct(
        EccubeConfig       $eccubeConfig
    )
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order_date', DateType::class, [
                'placeholder' => '',
                'format' => 'yyyy-MM-dd',
                'required' => false,
            ])
            ->add('arrival_date_schedule', DateType::class, [
                'placeholder' => '',
                'format' => 'yyyy-MM-dd',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InstockScheduleHeader::class,
        ]);
    }
}
