<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class NewsType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publish_date', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'html5' => false,
                'years' => range($this->eccubeConfig['eccube_news_start_year'], date('Y') + 3),
                'with_seconds' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range([
                        'min' => '0003-01-01',
                        'minMessage' => 'form_error.out_of_range',
                    ]),
                ],
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_mtext_len']]),
                ],
            ])
            ->add('thumbnail_path', FileType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img'
                ],
                'data_class' => null,
            ])
            ->add('link_method', CheckboxType::class, [
                'required' => false,
                'label' => 'admin.content.news.new_window',
                'value' => '1',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'purify_html' => true,
                'attr' => [
                    'rows' => 8,
                ],
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_ltext_len']]),
                ],
            ])
            ->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => ['admin.content.news.display_status__show' => true, 'admin.content.news.display_status__hide' => false],
                'required' => true,
                'expanded' => false,
            ])
            ->add('ThumbnailPathErrors', TextType::class, [
                'mapped' => false,
            ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateThumbnail']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
            'img' => ''
        ]);

        $resolver->setRequired('img');
        $resolver->setAllowedTypes('img', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_news';
    }

    public function validateThumbnail(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$event->getForm()->getConfig()->getOptions()['img']) {
            $form['ThumbnailPathErrors']->addError(new FormError('サムネイル画像をアップロードしてください。'));
        }
    }
}
