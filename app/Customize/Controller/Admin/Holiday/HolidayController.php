<?php

namespace Customize\Controller\Admin\Holiday;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\BusinessHoliday;
use Customize\Form\Type\Admin\HolidayType;
use Customize\Repository\BusinessHolidayRepository;
use DateTime;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class HolidayController extends AbstractController
{
    /**
     * @var BusinessHolidayRepository
     */
    protected $businessHolidayRepository;

    /**
     * ProductController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        BusinessHolidayRepository $businessHolidayRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->businessHolidayRepository = $businessHolidayRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Holiday list + create.
     * 
     * @Route("/%eccube_admin_route%/setting/shop/holiday", name="admin_setting_shop_holiday")
     * @Template("@admin/Holiday/index.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $builder = $this->formFactory->createBuilder(HolidayType::class);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $year = $request->get('holiday')['holiday_date']['year'];
            $month = $request->get('holiday')['holiday_date']['month'];
            $day = $request->get('holiday')['holiday_date']['day'];
            if (!$form->isValid()) {
                $this->addError('有効な日付を入力してください。', 'admin');

                return $this->redirectToRoute('admin_setting_shop_holiday', ['year' => $year]);
            }
            $holidayDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $year . '-'. $month . '-' . $day . ' ' . '00:00:00'
            );

            if ($holidayDate <= Carbon::now()) {
                $this->addError('未来の日付を入力してください。', 'admin');

                return $this->redirectToRoute('admin_setting_shop_holiday');
            }
            if ($this->businessHolidayRepository->findOneBy(['holiday_date' => $holidayDate])) {
                $this->addError('既に登録されている休日です', 'admin');

                return $this->redirectToRoute('admin_setting_shop_holiday', ['year' => $year]);
            }

            $Holiday = (new BusinessHoliday)
                ->setHolidayDate($holidayDate);
            // $em = $this->getDoctrine()->getManager();
            $em = $this->entityManager;
            $em->persist($Holiday);
            $em->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('admin_setting_shop_holiday', ['year' => $year]);
        }

        if ($request->get('year')) {
            $Results = $this->businessHolidayRepository->getFutureHolidays($request->get('year'));
            $year = $request->get('year');
        } else $Results = $this->businessHolidayRepository->getFutureHolidays(date('y'));
        $Holidays = $paginator->paginate(
            $Results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE_ADMIN)
        );

        return [
            'Holidays' => $Holidays,
            'form' => $form->createView(),
            'year' => $year ?? null
        ];
    }

    /**
     * Holiday delete.
     * 
     * @Route("/%eccube_admin_route%/setting/shop/holiday/{id}/delete", requirements={"id" = "\d+"}, name="admin_setting_shop_holiday_delete", methods={"DELETE"})
     */
    public function delete(Request $request, BusinessHoliday $Holiday)
    {
        $year = $Holiday->getHolidayDate()->format('Y');
        $this->isTokenValid();

        $em = $this->getDoctrine()->getManager();
        $em->remove($Holiday);
        $em->flush();

        $this->addSuccess('admin.common.delete_complete', 'admin');

        return $this->redirectToRoute('admin_setting_shop_holiday', ['year' => $year]);
    }
}
