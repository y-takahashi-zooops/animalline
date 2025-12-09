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

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\TaxType;
use Customize\Entity\InstockSchedule;
use Eccube\Entity\ProductClass;
use Eccube\Form\DataTransformer;
use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InstockScheduleType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * InstockScheduleType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EccubeConfig $eccubeConfig
     *
     * @throws \Exception
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    ) {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product_name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_mtext_len'],
                    ]),
                ],
            ])
            ->add('price', PriceType::class, [
                'accept_minus' => true,
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_int_len'],
                    ]),
                ],
            ])
            ->add('tax_rate', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0]),
                    new Assert\Regex([
                        'pattern' => "/^\d+(\.\d+)?$/u",
                        'message' => 'form_error.float_only',
                    ]),
                ],
            ]);

        $builder
            ->add($builder->create('tax_type', HiddenType::class)
                ->addModelTransformer(new DataTransformer\EntityToIdTransformer(
                    $this->entityManager,
                    TaxType::class
                )))
            ->add($builder->create('ProductClass', HiddenType::class)
                ->addModelTransformer(new DataTransformer\EntityToIdTransformer(
                    $this->entityManager,
                    ProductClass::class
                )));

        // 受注明細フォームの税率を補完する
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $InstockSchedule = $event->getData();
            if (empty($InstockSchedule['tax_rate'])) $InstockSchedule['tax_rate'] = 0;

            // JSから来た price が空扱いされているのを防ぐ
            if (!empty($InstockSchedule['ProductClass']) && empty($InstockSchedule['price'])) {
                $ProductClass = $this->entityManager
                    ->getRepository(ProductClass::class)
                    ->find($InstockSchedule['ProductClass']);

                if ($ProductClass) {
                    $InstockSchedule['price'] = $ProductClass->getItemCost();
                    // $InstockSchedule['price'] = $ProductClass->getPrice02();
                }
            }

            $event->setData($InstockSchedule);
        });

        // 商品追加後、price が null で送られてきた場合に自動で商品価格を補完
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if (!$data instanceof InstockSchedule) {
                return;
            }

            if ($data->getProductClass() && $data->getPrice() === null) {
                $data->setPrice($data->getProductClass()->getItemCost());
                // $data->setPrice($data->getProductClass()->getPrice02());
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InstockSchedule::class,
        ]);
    }

    /**
     * @param FormInterface $form
     * @param ConstraintViolationListInterface $errors
     */
    protected function addErrorsIfExists(FormInterface $form, ConstraintViolationListInterface $errors)
    {
        if (empty($errors)) {
            return;
        }

        foreach ($errors as $error) {
            $form->addError(new FormError(
                $error->getMessage(),
                $error->getMessageTemplate(),
                $error->getParameters(),
                $error->getPlural()
            ));
        }
    }
}
