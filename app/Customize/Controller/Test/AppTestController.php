<?php

namespace Customize\Controller\Test;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppTestController extends AbstractController
{
    /**
     * @Route("/test/crop_image", name="test_crop_image")
     * @Template("test/crop_image.twig")
     */
    public function crop()
    {
        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('image', FileType::class, [
                'label' => 'Image',
                'attr' => [
                    'onchange' => 'previewFile()'
                ]
            ])
            ->getForm();
        return [
            'form' => $form->createView(),
        ];
    }
}
