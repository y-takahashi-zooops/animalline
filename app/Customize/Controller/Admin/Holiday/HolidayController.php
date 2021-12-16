<?php

namespace Customize\Controller\Admin\Holiday;

use Customize\Entity\BusinessHoliday;
use Customize\Repository\BusinessHolidayRepository;
use DateTime;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HolidayController extends AbstractController
{
    /**
     * @var BusinessHolidayRepository
     */
    protected $businessHolidayRepository;

    public function __construct(
        BusinessHolidayRepository $businessHolidayRepository
    ) {
        $this->businessHolidayRepository = $businessHolidayRepository;
    }

    /**
     * Holiday list + create.
     * 
     * @Route("/%eccube_admin_route%/setting/shop/holiday", name="admin_setting_shop_holiday")
     * @Template("@admin/Holiday/index.twig")
     */
    public function index(Request $request)
    {
        $Holidays = $this->businessHolidayRepository->getFutureHolidays();

        if ('POST' === $request->getMethod()) {
            $holidayDate = DateTime::createFromFormat(
                'Y-m-d\TH:i',
                $request->get('holiday_date')
            );
            $Holiday = (new BusinessHoliday)->setHolidayDate($holidayDate);

            $em = $this->getDoctrine()->getManager();
            $em->persist($Holiday);
            $em->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('admin_setting_shop_holiday');
        }

        return [
            'Holidays' => $Holidays
        ];
    }

    /**
     * Holiday delete.
     * 
     * @Route("/%eccube_admin_route%/setting/shop/holiday/{id}/delete", requirements={"id" = "\d+"}, name="admin_setting_shop_holiday_delete", methods={"DELETE"})
     */
    public function delete(Request $request, BusinessHoliday $Holiday)
    {
        $this->isTokenValid();

        $em = $this->getDoctrine()->getManager();
        $em->remove($Holiday);
        $em->flush();

        $this->addSuccess('admin.common.delete_complete', 'admin');

        return $this->redirectToRoute('admin_setting_shop_holiday');
    }
}
