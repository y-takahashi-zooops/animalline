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

use Eccube\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubscriptionCustomerAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // お届け先住所のみで選択肢を作成
        /** @var Customer $Customer */
        $Customer = $options['customer'];
        $Addresses = $Customer->getCustomerAddresses()->toArray();

        // 定期注文のお届け先住所とマッチするものを初期選択とする
        /** @var SubscriptionContract $SubscriptionContract */
        $SubscriptionContract = $options['subscriptionContract'];
        $Checked = null;
        foreach ($Addresses as $Address) {
            if ($Address->getId() === $SubscriptionContract->getCustomerAddressId()) {
                $Checked = $Address;
            }
        }

        $builder->add('addresses', ChoiceType::class, [
            'choices' => $Addresses,
            'data' => $Checked,
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['customer' => null, 'subscriptionContract' => null]);
    }
}
