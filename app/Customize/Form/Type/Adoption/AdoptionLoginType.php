<?php

namespace Customize\Form\Type\Adoption;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Validator\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Eccube\Form\Type\RepeatedEmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Eccube\Form\Type\RepeatedPasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints as Assert;

class AdoptionLoginType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var AuthenticationUtils
     */
    protected $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils, EccubeConfig $eccubeConfig)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'attr' => [
                'max_length' => $this->eccubeConfig['eccube_stext_len'],
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
            ],
            'data' => $this->authenticationUtils->getLastUsername(),
        ]);
        $builder->add('login_memory', CheckboxType::class, [
            'required' => false,
        ]);
        $builder->add('password', PasswordType::class, [
            'attr' => [
                'max_length' => $this->eccubeConfig['eccube_stext_len'],
            ],
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adoption_login';
    }
}
