<?php

namespace Customize\Form\Type;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPets;
use Eccube\Common\EccubeConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConservationPetsType extends AbstractType
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
            ->add('pet_kind', ChoiceType::class, [
                'choices' =>
                [
                    '犬' => AnilineConf::ANILINE_PET_KIND_DOG,
                    '猫' => AnilineConf::ANILINE_PET_KIND_CAT
                ],
                'required' => true,
            ])
            ->add('breeds_type', EntityType::class, [
                'class' => 'Customize\Entity\Breeds',
                'choice_label' => function (\Customize\Entity\Breeds $breeds) {
                    return $breeds->getBreedsName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('pet_sex', ChoiceType::class, [
                'choices' =>
                [
                    '男の子' => AnilineConf::ANILINE_PET_SEX_MALE,
                    '女の子' => AnilineConf::ANILINE_PET_SEX_FEMALE
                ],
                'required' => true,
            ])
            ->add('pet_birthday', DateType::class)
            ->add('coat_color', EntityType::class, [
                'class' => 'Customize\Entity\CoatColors',
                'choice_label' => function (\Customize\Entity\CoatColors $coatColors) {
                    return $coatColors->getCoatColorName();
                },
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('future_wait', IntegerType::class)
            ->add('dna_check_result', IntegerType::class)
            ->add('pr_comment', TextType::class)
            ->add('description', TextType::class)
            ->add('delivery_time', TextType::class)
            ->add('delivery_way', TextType::class)
            ->add('thumbnail_path', FileType::class, [
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img1'
                ],
            ])
            ->add('image1', FileType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img2'
                ],
            ])
            ->add('image2', FileType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img3'
                ],
            ])
            ->add('image3', FileType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img4'
                ],
            ])
            ->add('image4', FileType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-inline',
                    'data-img' => 'img5'
                ],
            ])
            ->add('release_status', IntegerType::class)
            ->add('release_date', DateType::class)
            ->add('price', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConservationPets::class,
        ]);
    }
}
