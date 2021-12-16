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

namespace Customize\Service;

use Customize\Repository\BusinessHolidayRepository;

class BusinessCalendar
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
     * カレンダーデータ読込
     */
    public function getData($year, $month)
    {
        // 休日を全件取得
        $businessHolidays = $this->businessHolidayRepository->findAll();
        $thisMonthHolidays = array();

        // 対象年月に一致する休日のみを取得
        foreach ($businessHolidays as $businessHoliday) {
            $Holiday = $businessHoliday->getHolidayDate()->format('Y-m-d');

            $y = (int) date('Y', strtotime($Holiday));
            $m = (int) date('n', strtotime($Holiday));

            if ($y == $year && $m == $month) {
                // 日付を切り出し
                $d = date('d', strtotime($Holiday));
                array_push($thisMonthHolidays, $d);
            }
        }

        // 当月の日数を取得
        $target = new \DateTime("{$year}-{$month}-01");
        $endDay = intval($target->format('t'));

        // 月の日数分繰り返す
        for ($i = 0; $i < $endDay; $i++) {

            if ($i > 0) {
                $target->modify('+1day');
            }
            //定休日判定
            if (in_array($target->format('d'), $thisMonthHolidays)) {
                $holiday = true;
            } else {
                $holiday = false;
            }

            $dates[$i] = [
                'day' => intval($target->format('d')),  // 日
                'week' => $target->format('w'), // 曜日
                'holiday' => $holiday,          // 祝日フラグ
            ];
        }

        /*カレンダーの空欄箇所*/
        $first_date = current($dates);
        $end_date = end($dates);

        // 当月が月曜始まりでない場合、空白に前月の日を詰める
        $damy_num = intval($first_date['week']);
        if ($damy_num > 0) {
            // 前月の最終日を取得
            $month = $month - 1;
            $target = new \DateTime("{$year}-{$month}-01");
            $day = intval($target->format('t'));

            for ($i = $damy_num - 1; 0 <= $i; $i--) {
                array_unshift(
                    $dates,
                    [
                        'week' => $i,
                        'day' => $day,
                        'disabled' => true,
                        'holiday' => false
                    ]
                );
                $day--;
            }
        }

        // 当月が土曜終わりでない場合、空白に翌月の日を詰める
        $damy_num = intval($end_date['week']);
        if ($damy_num < 6) {
            $day = 1;
            for ($i = $damy_num; $i < 6; $i++) {
                array_push(
                    $dates,
                    [
                        'week' => $i,
                        'day' => $day,
                        'disabled' => true,
                        'holiday' => false
                    ]
                );
                $day++;
            }
        }
        return $dates;
    }
}
