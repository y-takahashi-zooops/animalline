<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\BusinessHoliday;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class HolidayType extends AbstractType
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
            ->add('holiday_date', DateType::class, [
                'required' => true,
                'input' => 'datetime',
                'years' => range(date('Y'), date('Y')+1),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BusinessHoliday::class,
        ]);
    }
}
