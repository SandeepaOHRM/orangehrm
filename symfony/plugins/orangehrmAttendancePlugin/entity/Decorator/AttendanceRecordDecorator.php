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

namespace OrangeHRM\Entity\Decorator;

use DateTime;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Entity\AttendanceRecord;
use OrangeHRM\Entity\Employee;

class AttendanceRecordDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    public const TIMEZONE_UTC = 'UTC';
    protected AttendanceRecord $attendanceRecord;

    private ?DateTime $punchInUserDateTime = null;
    private ?DateTime $punchInUTCDateTime = null;
    private ?DateTime $punchOutUserDateTime = null;
    private ?DateTime $punchOutUTCDateTime = null;

    /**
     * @param AttendanceRecord $attendanceRecord
     */
    public function __construct(AttendanceRecord $attendanceRecord)
    {
        $this->attendanceRecord = $attendanceRecord;
    }

    /**
     * @return AttendanceRecord
     */
    public function getAttendanceRecord(): AttendanceRecord
    {
        return $this->attendanceRecord;
    }

    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getAttendanceRecord()->setEmployee($employee);
    }

    /**
     * @return string
     */
    public function getAttendanceState(): string
    {
        return ucwords(strtolower($this->getAttendanceRecord()->getState()));
    }

    /**
     * @return string|null
     */
    public function getPunchInUTCDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToYmd($this->getAttendanceRecord()->getPunchInUtcTime());
    }

    /**
     * @return string|null
     */
    public function getPunchInUTCTime(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToTimeString(
            $this->getAttendanceRecord()->getPunchInUtcTime()
        );
    }

    /**
     * @return string|null
     */
    public function getPunchInUserDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToYmd($this->getAttendanceRecord()->getPunchInUserTime());
    }

    /**
     * @return string|null
     */
    public function getPunchInUserTime(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToTimeString(
            $this->getAttendanceRecord()->getPunchInUserTime()
        );
    }

    /**
     * @return string|null
     */
    public function getPunchOutUTCDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToYmd($this->getAttendanceRecord()->getPunchOutUtcTime());
    }

    /**
     * @return string|null
     */
    public function getPunchOutUTCtime(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToTimeString(
            $this->getAttendanceRecord()->getPunchOutUtcTime()
        );
    }

    /**
     * @return string|null
     */
    public function getPunchOutUserDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToYmd($this->getAttendanceRecord()->getPunchOutUserTime());
    }

    /**
     * @return string|null
     */
    public function getPunchOutUserTime(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToTimeString(
            $this->getAttendanceRecord()->getPunchOutUserTime()
        );
    }

    /**
     * @return DateTime|null
     */
    public function getPunchInUserDateTime(): ?DateTime
    {
        $punchInUserTime = $this->getAttendanceRecord()->getPunchInUserTime();
        if(is_null($punchInUserTime)){
            return null;
        }
        if(is_null($this->punchInUserDateTime) && is_object($punchInUserTime)){
            $this->punchInUserDateTime = new DateTime(
                $punchInUserTime->format('Y-m-d H:i:s'),
                $this->getDateTimeHelper()
                    ->getTimezoneByTimezoneOffset(
                        $this->getAttendanceRecord()->getPunchInTimeOffset()
                    )
            );
            $this->getAttendanceRecord()->setPunchInUserTime();
        }
        return $this->punchInUserDateTime;
    }

    /**
     * @param  DateTime|null  $punchInUserTime
     */
    public function setPunchInUserDateTime(?DateTime $punchInUserTime): void
    {
        $this->punchInUserDateTime = $punchInUserTime;
    }

    /**
     * @return DateTime|null
     */
    public function getPunchInUTCDateTime(): ?DateTime
    {
        $punchInUTCTime = $this->getAttendanceRecord()->getPunchInUtcTime();
        if(is_null($punchInUTCTime)){
            return null;
        }
        if(is_null($this->punchInUTCDateTime) && is_object($punchInUTCTime)){
            $this->punchInUTCDateTime = new DateTime(
                $punchInUTCTime->format('Y-m-d H:i:s'),
                $this->getDateTimeHelper()
                    ->getTimezoneByTimezoneOffset(
                        self::TIMEZONE_UTC)
            );
        }
        return $this->punchInUTCDateTime;
    }

    /**
     * @param  DateTime|null  $punchInUTCTime
     */
    public function setPunchInUTCDateTime(?DateTime $punchInUTCTime): void
    {
        $this->punchInUTCDateTime = $punchInUTCTime;
    }

    /**
     * @return DateTime|null
     */
    public function getPunchOutUserDateTime(): ?DateTime
    {
        $punchOutUserTime = $this->getAttendanceRecord()->getPunchOutUserTime();
        if(is_null($punchOutUserTime)){
            return null;
        }
        if(is_null($this->punchOutUserDateTime) && is_object($punchOutUserTime)){
            $this->punchOutUserDateTime = new DateTime(
                $punchOutUserTime->format('Y-m-d H:i:s'),
                $this->getDateTimeHelper()
                    ->getTimezoneByTimezoneOffset(
                        $this->getAttendanceRecord()->getPunchOutTimeOffset()
                    )
            );
        }
        return $this->punchOutUserDateTime;
    }

    /**
     * @param  DateTime|null  $punchOutUserTime
     */
    public function setPunchOutUserDateTime(?DateTime $punchOutUserTime): void
    {
        $this->punchOutUserDateTime = $punchOutUserTime;
    }

    /**
     * @return DateTime|null
     */
    public function getPunchOutUTCDateTime(): ?DateTime
    {
        $punchOutUTCTime = $this->getAttendanceRecord()->getPunchOutUtcTime();
        if(is_null($punchOutUTCTime)){
            return null;
        }
        if(is_null($this->punchOutUTCDateTime) && is_object($punchOutUTCTime)){
            $this->punchOutUTCDateTime = new DateTime(
                $punchOutUTCTime->format('Y-m-d H:i:s'),
                $this->getDateTimeHelper()
                    ->getTimezoneByTimezoneOffset(
                        self::TIMEZONE_UTC)
            );
        }
        return $this->punchOutUTCDateTime;
    }

    /**
     * @param  DateTime|null  $punchOutUTCTime
     */
    public function setPunchOutUTCDateTime(?DateTime $punchOutUTCTime): void
    {
        $this->punchOutUTCDateTime = $punchOutUTCTime;
    }
}
