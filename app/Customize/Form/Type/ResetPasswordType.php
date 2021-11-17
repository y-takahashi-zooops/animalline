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

namespace Customize\Form\Type;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Customer;
use Eccube\Form\Type\RepeatedPasswordType;
use Eccube\Form\Validator\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oldPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'max' => 32
                    ]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('password', RepeatedPasswordType::class, [
                'type' => PasswordType::class,
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'max' => 32
                    ]),
                    new Assert\NotBlank()
                ]
            ]);

//        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
//            $form = $event->getForm();
//            if ($form['re_password']['first']->getData() == $form['login_email']->getData()) {
//                $form['re_password']['first']->addError(new FormError(trans('common.password_eq_email')));
//            }
//        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'change_password';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' =>Customer::class,
        ]);
    }
}
