<?php

namespace Plugin\ZooopsSendmail\Form\Type\Admin;

use Plugin\ZooopsSendmail\Entity\MailTemplate;
use Plugin\ZooopsSendmail\Repository\MailTemplateRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\File\File;

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

        $builder->add('attach_file', FileType::class, [
            'multiple' => false,
            'mapped' => false,
        ]);

        $builder->add('template_attach', HiddenType::class);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event){
                $form = $event->getForm();
                $template = $event->getData();

                if($template instanceof MailTemplate) {
                    if($template->getTemplateAttach()) {
                        // アップロード済みのファイルをFileTypeにセット
                        $form["attach_file"]->setData(
                            new File("var/tmp/mail/".$template->getTemplateAttach())
                        );
                    }
                }
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
                $form = $event->getForm();
                $template = $event->getData();

                if($template instanceof MailTemplate) {
                    $file = $form["attach_file"]->getData();
                    var_dump($file);
                    $filename = $file->getClientOriginalName();

                    if($file) {
                        // ファイルアップロード
                        $file->move("var/tmp/mail/",$filename);
                        $template->setTemplateAttach($filename);
                    }
                }
            })
        ;
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
