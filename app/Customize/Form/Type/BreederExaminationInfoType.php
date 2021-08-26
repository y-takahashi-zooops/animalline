<?php

namespace Customize\Form\Type;

use Customize\Entity\BreederExaminationInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Customize\Config\AnilineConf;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class BreederExaminationInfoType extends AbstractType
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
            ->add('pedigree_organization', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    '団体名' => 1_2,
                    'なし'  => AnilineConf::PEDIGREE_ORGANIZATION_NONE,
                    'その他' => AnilineConf::PEDIGREE_ORGANIZATION_OTHER,
                ],
                'mapped' => false,
                'expanded' => true,
            ])
            ->add('group_organization', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    '----' => '',
                    'JKC' => AnilineConf::PEDIGREE_ORGANIZATION_JKC,
                    'KC'  => AnilineConf::PEDIGREE_ORGANIZATION_KC,
                ],
                'mapped' => false,
            ])
            ->add('pedigree_organization_other', TextType::class, [
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('breeding_pet_count', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_count_1', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_count_2', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_count_3', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_buy_place_1', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_buy_place_2', IntegerType::class, [
                'required' => true,
            ])
            ->add('parent_pet_buy_place_3', IntegerType::class, [
                'required' => true,
            ])
            ->add('owner_worktime_ave', IntegerType::class, [
                'required' => true,
            ])
            ->add('family_staff_count', IntegerType::class, [
                'required' => true,
            ])
            ->add('family_staff_worktime_ave', IntegerType::class, [
                'required' => true,
            ])
            ->add('fulltime_staff_count', IntegerType::class, [
                'required' => true,
            ])
            ->add('fulltime_staff_worktime_ave', IntegerType::class, [
                'required' => true,
            ])
            ->add('parttime_staff_count', IntegerType::class, [
                'required' => true,
            ])
            ->add('parttime_staff_worktime_ave', IntegerType::class, [
                'required' => true,
            ])
            ->add('other_staff_count', IntegerType::class, [
                'required' => true,
            ])
            ->add('other_staff_worktime_ave', IntegerType::class, [
                'required' => true,
            ])
            ->add('breeding_exp_year', IntegerType::class, [
                'required' => true,
            ])
            ->add('breeding_exp_month', ChoiceType::class, [
                'choices' => range(0, 11),
                'required' => true,
            ])
            ->add('is_participate_show', ChoiceType::class, [
                'choices' => [
                    'なし' => 0,
                    'あり' => 1,
                ],
                'required' => true,
            ])
            ->add('cage_size_1', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])->add('cage_size_2', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])->add('cage_size_3', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('cage_size_other', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_stext_len'],
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ]
            ])
            ->add('breeding_experience', ChoiceType::class, [
                'choices' =>
                [
                    'なし' => AnilineConf::EXPERIENCE_NONE,
                    '1～4回' => AnilineConf::EXPERIENCE_TO_FOUR,
                    '5～9回' => AnilineConf::EXPERIENCE_TO_NINE,
                    '10～19回' => AnilineConf::EXPERIENCE_TO_NINETEEN,
                    '20～49回' => AnilineConf::EXPERIENCE_TO_FORTYNINE,
                    '50回以上' => AnilineConf::EXPERIENCE_NONE,
                ],
                'attr' => [
                    'class' => 'ec-radio',
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('selling_experience', ChoiceType::class, [
                'choices' =>
                [
                    'なし' => AnilineConf::EXPERIENCE_NONE,
                    '1～4回' => AnilineConf::EXPERIENCE_TO_FOUR,
                    '5～9回' => AnilineConf::EXPERIENCE_TO_NINE,
                    '10～19回' => AnilineConf::EXPERIENCE_TO_NINETEEN,
                    '20～49回' => AnilineConf::EXPERIENCE_TO_FORTYNINE,
                    '50回以上' => AnilineConf::EXPERIENCE_NONE,
                ],
                'attr' => [
                    'class' => 'ec-radio',
                ],
                'expanded' => true,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederExaminationInfo::class,
        ]);
    }
}