<?php

namespace Plugin\ZooopsSendmail\Form\Type\Admin;

use Plugin\ZooopsSendmail\Entity\MailTemplate;
use Plugin\ZooopsSendmail\Repository\MailTemplateRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailTemplateType extends AbstractType
{

    /**
     * MailTemplateType constructor.
     *
     * @param MailTemplateRepository $templateRepository
     */
    public function __construct(MailTemplateRepository $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();

        //テンプレート選択状態
        $template_selector_default = array();
        if(isset($entity)){
            $template_selector_default = array($entity->getId());
        }

        //ドロップダウン準備
        $templates = $this->templateRepository->findAll();

        $choices = array('新規テンプレート' => 0);

        foreach ($templates as $template) {
            $choices[$template->getTemplateName()] = $template->getId();
        }

		$builder->add('template_selector', ChoiceType::class, [
            'choices'  => $choices,
            'mapped' => false,
            'preferred_choices' => $template_selector_default,
        ]);
        //ドロップダウン準備

        $builder->add('template_name', TextType::class,[
            'constraints' => [
                new NotBlank(),
            ],
            'required' => false
        ]);
        $builder->add('id', HiddenType::class,[]);

        $builder->add('template_title', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 255]),
            ],
            'required' => false
        ]);

		$builder->add('template_detail', TextareaType::class, [
            'constraints' => [
                new NotBlank(),
            ],
            'required' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MailTemplate::class,
        ]);
    }
}
