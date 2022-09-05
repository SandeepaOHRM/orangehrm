<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Dashboard\Service;

use DateInterval;
use DateTime;
use Exception;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Dashboard\Dao\EmployeeTimeAtWorkDao;
use OrangeHRM\Dashboard\Dto\TimeAtWorkLastActionDetails;
use OrangeHRM\Entity\AttendanceRecord;
use OrangeHRM\Time\Service\TimesheetPeriodService;

class EmployeeTimeAtWorkService
{
    use DateTimeHelperTrait;
    use AuthUserTrait;

    public const STATE_PUNCHED_IN = 'PUNCHED IN';
    public const STATE_PUNCHED_OUT = 'PUNCHED OUT';

    /**
     * @var EmployeeTimeAtWorkDao
     */
    private EmployeeTimeAtWorkDao $employeeTimeAtWorkDao;

    /**
     * @var TimesheetPeriodService
     */
    private TimesheetPeriodService $timesheetPeriodService;

    /**
     * @var int
     */
    private int $totalTimeForWeek = 0;

    public function getEmployeeTimeAtWorkDao(): EmployeeTimeAtWorkDao
    {
        return $this->employeeTimeAtWorkDao ??= new EmployeeTimeAtWorkDao();
    }

    /**
     * @return TimesheetPeriodService
     */
    private function getTimesheetPeriodService(): TimesheetPeriodService
    {
        return $this->timesheetPeriodService ??= new TimesheetPeriodService();
    }

    /**
     * @param int $empNumber
     * @return array
     * @throws Exception
     */
    public function getTimeAtWorkData(int $empNumber): array
    {
        return $this->getDataForCurrentWeek($empNumber);
    }

    /**
     * @param int $empNumber
     * @return array
     * @throws Exception
     */
    public function getTimeAtWorkMetaData(int $empNumber): array
    {
        //TODO::Determine whether current date is server date or date from the client side
        $currentDate = $this->getDateTimeHelper()->getNow();
        $totalTimeForCurrentDay = $this->getTotalTimeForGivenDate($empNumber, $currentDate);
        list($weekStartDate, $weekEndDate) = $this->extractStartDateAndEndDateFromDate($currentDate);

        $weekStartDate = new DateTime($weekStartDate);
        $weekEndDate = new DateTime($weekEndDate);

        return [
            'lastAction' => $this->getLastActionDetails($empNumber),
            'currentDay' => [
                'currentDate' => $this->getDateDetails($currentDate),
                'totalTime' => $this->getTimeInHoursAndMinutes($totalTimeForCurrentDay)
            ],
            'currentWeek' => [
                'startDate' => $this->getDateDetails($weekStartDate),
                'endDate' => $this->getDateDetails($weekEndDate),
                'totalTime' => $this->getTimeInHoursAndMinutes($this->totalTimeForWeek)
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @return array eg:- returns ['date' => 2022-09-05, 'label' => 'Sep 05']
     */
    private function getDateDetails(DateTime $dateTime): array
    {
        return [
            'date' => $this->getDateTimeHelper()->formatDate($dateTime),
            'label' => $dateTime->format('M') . ' ' . $dateTime->format('d')
        ];
    }

    /**
     * @param int $timeInMinutes
     * @return array eg:- for 80 minutes, this returns [ 'hours' => 1, 'minutes => 10 ]
     */
    private function getTimeInHoursAndMinutes(int $timeInMinutes): array
    {
        return [
            'hours' => floor($timeInMinutes / 60),
            'minutes' => $timeInMinutes % 60
        ];
    }

    /**
     * @param int $empNumber
     * @return array
     */
    private function getLastActionDetails(int $empNumber): array
    {
        $attendanceRecord = $this->getEmployeeTimeAtWorkDao()->getLatestAttendanceRecordByEmpNumber($empNumber);
        if (!$attendanceRecord instanceof AttendanceRecord) {
            $actionDetails = new TimeAtWorkLastActionDetails();
        } elseif ($attendanceRecord->getState() === self::STATE_PUNCHED_IN) {
            $actionDetails = new TimeAtWorkLastActionDetails(
                $attendanceRecord->getState(),
                $attendanceRecord->getDecorator()->getPunchInUTCDate(),
                $attendanceRecord->getDecorator()->getPunchInUTCTime(),
                $attendanceRecord->getDecorator()->getPunchInUserDate(),
                $attendanceRecord->getDecorator()->getPunchInUserTime(),
                $attendanceRecord->getPunchInTimeOffset()
            );
        } else {
            $actionDetails = new TimeAtWorkLastActionDetails(
                $attendanceRecord->getState(),
                $attendanceRecord->getDecorator()->getPunchOutUTCDate(),
                $attendanceRecord->getDecorator()->getPunchOutUTCTime(),
                $attendanceRecord->getDecorator()->getPunchOutUserDate(),
                $attendanceRecord->getDecorator()->getPunchOutUserTime(),
                $attendanceRecord->getPunchOutTimeOffset()
            );
        }
        return [
            'state' => $actionDetails->getState(),
            'utcDate' => $actionDetails->getUtcDate(),
            'utcTime' => $actionDetails->getUtcTime(),
            'userDate' => $actionDetails->getUserDate(),
            'userTime' => $actionDetails->getUserTime(),
            'timezoneOffset' => $actionDetails->getTimezoneOffset()
        ];
    }

    /**
     * @param int $empNumber
     * @param DateTime $givenDateTime
     * @return int total time will be returned in minutes
     * @throws Exception
     */
    public function getTotalTimeForGivenDate(int $empNumber, DateTime $givenDateTime): int
    {
        $totalTime = 0;
        $attendanceRecords = $this->getEmployeeTimeAtWorkDao()
            ->getAttendanceRecordsByEmployeeAndDate($empNumber, $givenDateTime);
        /**
         * No attendance records found for given day
         */
        if (!$attendanceRecords) {
            return $totalTime;
        }

        $givenDate = $this->getDateTimeHelper()->formatDate($givenDateTime);
        $givenDateLowerBoundary = new DateTime($givenDate . ' ' . '00:00:00');

        foreach ($attendanceRecords as $attendanceRecord) {
            if ($attendanceRecord->getState() === self::STATE_PUNCHED_OUT) {
                $punchInUserDateTime = $attendanceRecord->getPunchInUserTime();
                $punchOutUserDateTime = $attendanceRecord->getPunchOutUserTime();

                /**
                 * Given day 2022-09-05
                 * When punched-in given day and punched-out next day
                 * eg:- punched-in on 2022-09-05 at 23:30 and punched-out on 2022-09-06 at 00:30
                 * 30 minutes goes to 2022-09-05 total time and 30 minutes goes to 2022-09-06 total time
                 */
                if ($this->getDateTimeHelper()->formatDate($punchOutUserDateTime) > $givenDate) {
                    $generalLowerBoundary = clone $givenDateLowerBoundary;
                    $punchInDateUpperBoundary = $generalLowerBoundary->add(new DateInterval('P1D'));
                    $totalTime = $totalTime + $this->getTimeDifference($punchInDateUpperBoundary, $punchInUserDateTime);
                } /**
                 * Given day 2022-09-05
                 * When punched-out in given day and punched-in in previous day
                 * eg:- punched-in on 2022-09-04 at 23:30 and punched-out on 2022-09-05 at 00:30
                 * 30 minutes goes to 2022-09-04 total time and 30 minutes goes to 2022-09-05 total time
                 */
                elseif ($this->getDateTimeHelper()->formatDate($punchInUserDateTime) < $givenDate) {
                    $totalTime = $totalTime + $this->getTimeDifference($givenDateLowerBoundary, $punchOutUserDateTime);
                } /**
                 * Punched-in and punched-out in the given day
                 */
                else {
                    $totalTime = $totalTime + $this->getTimeDifference($punchOutUserDateTime, $punchInUserDateTime);
                }
            }
        }
        return $totalTime;
    }

    /**
     * @param DateTime $date
     * @return array  eg:- array(if monday as first day in config => '2021-12-13', '2021-12-19')
     */
    private function extractStartDateAndEndDateFromDate(DateTime $date): array
    {
        $currentWeekFirstDate = date('Y-m-d', strtotime('monday this week', strtotime($date->format('Y-m-d'))));
        $configDate = $this->getTimesheetPeriodService()->getTimesheetStartDate() - 1;
        $startDate = date('Y-m-d', strtotime($currentWeekFirstDate . ' + ' . $configDate . ' days'));
        $endDate = date('Y-m-d', strtotime($startDate . ' + 6 days'));
        return [$startDate, $endDate];
    }

    /**
     * @param int $empNumber
     * @return array
     * @throws Exception
     */
    private function getDataForCurrentWeek(int $empNumber): array
    {
        list($startDate) = $this->extractStartDateAndEndDateFromDate($this->getDateTimeHelper()->getNow());
        $counter = 0;
        $date = new DateTime($startDate);
        $weeklyData = [];
        while ($counter < 7) {
            $totalTimeForDay = $this->getTotalTimeForGivenDate($empNumber, $date);
            $weeklyData[] = [
                'workDay' => [
                    'id' => $date->format('w'),
                    'day' => $date->format('D'),
                    'date' => $this->getDateTimeHelper()->formatDate($date),
                ],
                'totalTime' => $this->getTimeInHoursAndMinutes($totalTimeForDay),
            ];
            $date = clone $date;
            $date = $date->add(new DateInterval('P1D'));
            $this->totalTimeForWeek = $this->totalTimeForWeek + $totalTimeForDay;
            $counter++;
        }
        return $weeklyData;
    }

    /**
     * @param DateTime $endDateTime
     * @param DateTime $startDateTime
     * @return int difference will be given in minutes
     */
    private function getTimeDifference(DateTime $endDateTime, DateTime $startDateTime): int
    {
        return $endDateTime->diff($startDateTime)->h * 60 + $endDateTime->diff($startDateTime)->i;
    }

}
