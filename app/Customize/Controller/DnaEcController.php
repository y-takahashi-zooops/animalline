<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller;

use Customize\Form\Type\TrainingType;
use Customize\Service\MailService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\NewsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CategoryRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckKindsEcRepository;

class DnaEcController extends AbstractController
{
    /**
     * @var NewsRepository
     */
    protected $NewsRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductListOrderByRepository
     */
    protected $productListOrderByRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var DnaCheckKindsEcRepository
     */
    protected $dnaCheckKindsEcRepository;

    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(
        NewsRepository $NewsRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ProductListOrderByRepository $productListOrderByRepository,
        MailService $mailService,
        BreedsRepository $breedsRepository,
        DnaCheckKindsEcRepository $dnaCheckKindsEcRepository
    ) {
        $this->NewsRepository = $NewsRepository;
        $this->productListOrderByRepository = $productListOrderByRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->mailService = $mailService;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckKindsEcRepository = $dnaCheckKindsEcRepository;
    }
    /**
     * @Route("/dnaec", name="dna_ec_top")
     * @Template("dna_ec.twig")
     */
    public function dna_ec()
    {
        $breeds = $this->breedsRepository->findAll();

        return [
            'breeds' => $breeds
        ];
    }
}
