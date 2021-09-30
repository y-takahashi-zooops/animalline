<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\StockWaste;
use Customize\Repository\StockWasteReasonRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Eccube\Common\EccubeConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
class StockWasteType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var StockWasteReasonRepository
     */
    protected $stockWasteReasonRepository;

    public function __construct(EccubeConfig $eccubeConfig, StockWasteReasonRepository $stockWasteReasonRepository)
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->stockWasteReasonRepository = $stockWasteReasonRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('waste_date', DateType::class, [
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--']
            ])
            ->add('waste_unit', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('stock_waste_reason', EntityType::class, [
                'class' => 'Customize\Entity\StockWasteReason',
                'placeholder' => 'common.select',
                'choice_label' => function (\Customize\Entity\StockWasteReason $stockWasteReason) {
                    return $stockWasteReason->getWasteReason();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
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
