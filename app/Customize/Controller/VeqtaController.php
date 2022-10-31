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

use Carbon\Carbon;
use Customize\Repository\DnaCheckKindsRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckStatusDetail;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\VeqtaPdfService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Service\VeqtaQueryService;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Service\MailService;
use Eccube\Repository\CustomerRepository;

class VeqtaController extends AbstractController
{
    
}
