<?php

namespace Customize\Form\Type\Admin;

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
        $builder->setDisabled(true)
            ->add('pedigree_organization_other', TextType::class)
            ->add('pedigree_organization', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'なし' => AnilineConf::PEDIGREE_ORGANIZATION_NONE,
                    'JKC' => AnilineConf::PEDIGREE_ORGANIZATION_JKC,
                    'KC'  => AnilineConf::PEDIGREE_ORGANIZATION_KC,
                    'OTHER' => AnilineConf::PEDIGREE_ORGANIZATION_OTHER,
                ],
            ])
            ->add('parent_pet_count_1', IntegerType::class, [
                'required' => false,
            ])
            ->add('parent_pet_count_2', IntegerType::class, [
                'required' => false,
            ])
            ->add('parent_pet_count_3', IntegerType::class, [
                'required' => false,
            ])
            ->add('parent_pet_buy_place_1', IntegerType::class, [
                'required' => false,
            ])
            ->add('parent_pet_buy_place_2', IntegerType::class, [
                'required' => false,
            ])
            ->add('parent_pet_buy_place_3', IntegerType::class, [
                'required' => false,
            ])
            ->add('owner_worktime_ave', IntegerType::class, [
                'required' => false,
            ])
            ->add('family_staff_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('family_staff_worktime_ave', IntegerType::class, [
                'required' => false,
            ])
            ->add('fulltime_staff_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('fulltime_staff_worktime_ave', IntegerType::class, [
                'required' => false,
            ])
            ->add('parttime_staff_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('parttime_staff_worktime_ave', IntegerType::class, [
                'required' => false,
            ])
            ->add('other_staff_count', IntegerType::class, [
                'required' => false,
            ])
            ->add('other_staff_worktime_ave', IntegerType::class, [
                'required' => false,
            ])
            ->add('breeding_exp_year', IntegerType::class, [
                'required' => false,
            ])
            ->add('breeding_exp_month', IntegerType::class)
            ->add('is_participate_show', ChoiceType::class, [
                'choices' => [
                    'なし' => AnilineConf::NONE,
                    'あり' => AnilineConf::CAN_BE,
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => false,
                'required' => false,
            ])
            ->add('cage_size_1', CheckboxType::class, [
                'label' => '休憩場所としてのみ利用できる小さいサイズ(分離型)',
                'required' => false,
            ])->add('cage_size_2', CheckboxType::class, [
                'label' => '休憩場所と運動スペースを兼ねることのできる大きいサイズ(一体型)',
                'required' => false,
            ])->add('cage_size_3', CheckboxType::class, [
                'label' => 'その他',
                'required' => false,
            ])
            ->add('cage_size_other', TextType::class, [
                'required' => false,
            ])
            ->add('exercise_status', ChoiceType::class, [
                'choices' => [
                    '毎日1回はケージから出して運動させている' => 1,
                    '毎日ではないが、ケージから出して運動させている' => 2,
                    'ケージから出せない' => 3,
                    'その他' => 4,
                ],
                'expanded' => false,
                'required' => false,
            ])
            ->add('exercise_status_other', TextType::class, [
                'required' => false,
            ])
            ->add('publish_pet_count', IntegerType::class, [
                'required' => false,
            ])->add('breeding_experience', ChoiceType::class, [
                'choices' => [
                    'なし' => AnilineConf::EXPERIENCE_NONE,
                    '1～4回' => AnilineConf::EXPERIENCE_TO_FOUR,
                    '5～9回' => AnilineConf::EXPERIENCE_TO_NINE,
                    '10～19回' => AnilineConf::EXPERIENCE_TO_NINETEEN,
                    '20～49回' => AnilineConf::EXPERIENCE_TO_FORTYNINE,
                    '50回以上' => AnilineConf::EXPERIENCE_GREATER_FIFTY,
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => false,
                'required' => false,
            ])
            ->add('selling_experience', ChoiceType::class, [
                'choices' => [
                    'なし' => AnilineConf::EXPERIENCE_NONE,
                    '1～4回' => AnilineConf::EXPERIENCE_TO_FOUR,
                    '5～9回' => AnilineConf::EXPERIENCE_TO_NINE,
                    '10～19回' => AnilineConf::EXPERIENCE_TO_NINETEEN,
                    '20～49回' => AnilineConf::EXPERIENCE_TO_FORTYNINE,
                    '50回以上' => AnilineConf::EXPERIENCE_GREATER_FIFTY,
                ],
                'attr' => [
                    'class' => 'form-check-inline ec-radio'
                ],
                'expanded' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BreederExaminationInfo::class,
        ]);
    }
}
