<?php

namespace Customize\Controller\Test;

use Customize\Config\AnilineConf;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
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
                'label' => 'Image'
            ])
            ->getForm();
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/test/upload_image", name="upload_crop_image", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        if (!file_exists(AnilineConf::ANILINE_IMAGE_URL_BASE . '/test/')) {
            mkdir(AnilineConf::ANILINE_IMAGE_URL_BASE . '/test/', 0777, 'R');
        }
        $folderPath = AnilineConf::ANILINE_IMAGE_URL_BASE . '/test/';
        $image_parts = explode(";base64,", $_POST['image']);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath . uniqid() . '.' . $image_type;
        file_put_contents($file, $image_base64);
        return new JsonResponse("image uploaded successfully.");
    }
}
