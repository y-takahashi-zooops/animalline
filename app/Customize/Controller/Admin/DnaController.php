<?php

namespace Customize\Controller\Admin;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class DnaController extends AbstractController
{
    /**
     * DnaController constructor
     * 
     */
    public function __construct() {
    }

    /**
     * @Route("/%eccube_admin_route%/dna/examination_status", name="admin_dna_examination_status")
     * @Template("@admin/DNA/examination_status.twig")
     */
    public function examination_status()
    {
    }
}
