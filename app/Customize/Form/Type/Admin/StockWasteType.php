<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\StockWaste;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;

class StockWasteType extends AbstractType
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
            ->add('waste_date', DateType::class, [
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('waste_unit', IntegerType::class, [
                'required' => true,
            ])
            ->add('comment', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'rows' => 5
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockWaste::class,
        ]);
    }
}
