<?php

namespace Plugin\ZooopsSendmail\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\ZooopsSendmail\Form\Type\Admin\ConfigType;
use Plugin\ZooopsSendmail\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository, EntityManagerInterface $entityManager)
    {
        $this->configRepository = $configRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/%eccube_admin_route%/zooops_sendmail/config", name="zooops_sendmail_admin_config")
     * @Template("@ZooopsSendmail/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('zooops_sendmail_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
