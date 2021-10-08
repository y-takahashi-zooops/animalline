<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\ProductMaker;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductMakerType extends AbstractType
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
        ->add('maker_name', TextType::class, [
            'required' => true,
            'attr' => [
                'maxlength' => 20,
            ],
            'constraints' => [
                new Assert\Length([
                    'max' => 20,
                ]),
                new Assert\NotBlank()
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductMaker::class,
        ]);
    }
}
