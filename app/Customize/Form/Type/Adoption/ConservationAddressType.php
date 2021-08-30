<?php

namespace Customize\Form\Type\Adoption;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\PrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationAddressType extends AbstractType
{
    /**
     * @var array
     */
    protected $config;

    /**
     * {@inheritdoc}
     *
     * AddressType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->config = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['pref_options']['required'] = $options['required'];
        $options['addr01_options']['required'] = $options['required'];
        $options['addr02_options']['required'] = $options['required'];
        $options['addr03_options']['required'] = $options['required'];

        // required の場合は NotBlank も追加する
        if ($options['required']) {
            $options['pref_options']['constraints'] = array_merge([
                new Assert\NotBlank([]),
            ], $options['pref_options']['constraints']);

            $options['addr01_options']['constraints'] = array_merge([
                new Assert\NotBlank([]),
            ], $options['addr01_options']['constraints']);

            $options['addr02_options']['constraints'] = array_merge([
                new Assert\NotBlank([]),
            ], $options['addr02_options']['constraints']);

            $options['addr03_options']['constraints'] = array_merge(
                [
                    new Assert\NotBlank([]),
                ],
                $options['addr03_options']['constraints']
            );
        }

        if (!isset($options['options']['error_bubbling'])) {
            $options['options']['error_bubbling'] = $options['error_bubbling'];
        }

        $builder
            ->add($options['pref_name'], PrefType::class, array_merge_recursive($options['options'], $options['pref_options']))
            ->add($options['addr01_name'], TextType::class, array_merge_recursive($options['options'], $options['addr01_options']))
            ->add($options['addr02_name'], TextType::class, array_merge_recursive($options['options'], $options['addr02_options']))
            ->add($options['addr03_name'], TextType::class, array_merge_recursive($options['options'], $options['addr03_options']));

        $builder->setAttribute('pref_name', $options['pref_name']);
        $builder->setAttribute('addr01_name', $options['addr01_name']);
        $builder->setAttribute('addr02_name', $options['addr02_name']);
        $builder->setAttribute('addr03_name', $options['addr03_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $builder = $form->getConfig();
        $view->vars['pref_name'] = $builder->getAttribute('pref_name');
        $view->vars['addr01_name'] = $builder->getAttribute('addr01_name');
        $view->vars['addr02_name'] = $builder->getAttribute('addr02_name');
        $view->vars['addr03_name'] = $builder->getAttribute('addr03_name');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'options' => [],
            'pref_options' => ['constraints' => [], 'attr' => ['class' => 'p-region-id']],
            'addr01_options' => [
                'constraints' => [
                    new Assert\Length(['max' => $this->config['eccube_city_len']]),
                ],
                'attr' => [
                    'maxlength' => $this->config['eccube_city_len'],
                    'class' => 'p-locality p-street-address',
                    'placeholder' => 'common.address_sample_01',
                ],
            ],
            'addr02_options' => [
                'constraints' => [
                    new Assert\Length(['max' => $this->config['eccube_address1_len']]),
                ],
                'attr' => [
                    'class' => 'p-extended-address',
                    'placeholder' => 'common.address_sample_02',
                ],
            ],
            'addr03_options' => [
                'constraints' => [
                    new Assert\Length(['max' => $this->config['eccube_address2_len']]),
                ],
                'attr' => [
                    'class' => 'p-extended-address',
                    'placeholder' => 'common.address_sample_02',
                ],
            ],
            'pref_name' => 'PrefId',
            'addr01_name' => 'city',
            'addr02_name' => 'address',
            'addr03_name' => 'building',
            'error_bubbling' => false,
            'inherit_data' => true,
            'trim' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'addr';
    }
}
