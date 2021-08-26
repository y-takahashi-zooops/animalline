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
            ->add('pedigree_organization', RadioType::class, [
                'required' => true,
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
            ->add('cage_size_1', CheckboxType::class, [
                'label' => '休憩場所としてのみ利用できる小さいサイズ(分離型)',
                'required' => false,
            ])->add('cage_size_2', CheckboxType::class, [
                'required' => false,
            ])->add('cage_size_3', CheckboxType::class, [
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