<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Customize\Service\BusinessCalendar;

class CalendarController extends AbstractController
{
    /**
     * @var BusinessCalendar
     */
    protected $businessCalendar;

    /**
     * CallendarController constructor.
     *
     * @param BusinessCalendar $businessCalendar
     */
    public function __construct(
        BusinessCalendar $businessCalendar
    ) {
        $this->businessCalendar = $businessCalendar;
    }

    /** ajax通信用
     * @Route("/exprocess/get_bcalendar", name="calendar")
     */
    public function index(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $year = $request->get('year');
        $month = $request->get('month') + 1;

        $dates = $this->businessCalendar->getData($year, $month);

        return $this->json([
            'year' => $year,
            'month' => $month,
            'dates' => $dates,
        ]);
    }
}
