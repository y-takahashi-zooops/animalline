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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;

class ChangeSubscriptionType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $config;

    /**
     * @var EntityManager
     */
    protected $em;
   
    /**
     * @var \Plugin\ZooopsSubscription\Entity\SubscriptionContract
     */
    protected $SubscriptionContract = null;

    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine, EccubeConfig $config)
    {
        $this->doctrine = $doctrine;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nextDeliveryDate = $options['data']['next_delivery_date'];

        if($nextDeliveryDate){
            $nextDeliveryDateMax = new \DateTime($nextDeliveryDate->format('Y-m-d'));
            $nextDeliveryDateMax = $nextDeliveryDateMax->modify('+ 99days');
        }else{
            $nextDeliveryDate = new \DateTime();
            $nextDeliveryDateMax = new \DateTime();
        }

        $builder
            // 変更項目ラジオボタン
            ->add('change_element', ChoiceType::class, [
                'choices' => [
                    'お届けサイクルの変更' => 'subscription_cycle',
                    '次回お届け日の変更' => 'next_delivery'
                ],
                'data' => 'subscription_cycle',
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
            ])
            // 間隔ラジオボタン
            ->add('cycle_type', ChoiceType::class, [
                'choices' => [
                    '月間隔で指定' => 'month',
                    '日間隔で指定' => 'day'
                ],
                'data' => 'month',
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
            ])
            // 月間隔
            ->add('month', ChoiceType::class, [
                'choices'  =>
                [
                    '毎月' => 1,
                    '２ヶ月毎' => 2,
                    '３ヶ月毎' => 3,
                    '４ヶ月毎' => 4,
                    '５ヶ月毎' => 5,
                    '６ヶ月毎' => 6
                ],
                'placeholder' => false,
                'mapped' => false,
            ])
            // 日間隔
            ->add('day', IntegerType::class, [
                'data' => 10,
                'attr' => [
                    'min' => 10,
                    'max' => 99,
                    'maxlength' => $this->config['eccube_int_len'],
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual([
                        'value' => 10,
                    ]),
                    new Assert\Regex(['pattern' => '/^\d+$/']),
                ],
                'mapped' => false,
            ])
            // 次回配送日
            ->add('next_delivery_date', DateType::class, [
                'required' => true,
                'input' => 'datetime',
                'data' => $nextDeliveryDate,
                'widget' => 'single_text',
                'attr' => [
                    "min" => $nextDeliveryDate->format('Y-m-d'),
                    "max" => $nextDeliveryDateMax->format('Y-m-d'),
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            // 購入スパン
            ->add('repeat_span', HiddenType::class, [
                'mapped' => true,
            ])
            // 購入スパン単位
            ->add('span_unit', HiddenType::class, [
                'mapped' => true,
            ]);

        // お届けサイクルの変更の場合、各値を設定
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
           
            $data = $event->getData();

            if ($data['change_element'] == 'subscription_cycle') {
                // 日間隔の場合、日を購入スパンに設定
                if ($data['cycle_type'] == 'day') {
                    $spanUnit = 0;
                    $repeatSpan = $data['day'];
                // 月間隔の場合、月を購入スパンに設定
                } elseif ($data['cycle_type'] == 'month') {
                    $spanUnit = 1;
                    $repeatSpan = $data['month'];
                }

                $data['span_unit'] = $spanUnit;
                $data['repeat_span'] = $repeatSpan;
                // $data['next_delivery_date'] = null;
                $event->setData($data);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'change_subscription';
    }
}
