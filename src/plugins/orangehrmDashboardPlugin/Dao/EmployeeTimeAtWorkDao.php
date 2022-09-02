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

namespace OrangeHRM\Dashboard\Dao;

use DateTime;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\AttendanceRecord;
use OrangeHRM\ORM\ListSorter;

class EmployeeTimeAtWorkDao extends BaseDao
{
    /**
     * @param int $empNumber
     * @return AttendanceRecord|null
     */
    public function getLatestAttendanceRecordByEmpNumber(int $empNumber): ?AttendanceRecord
    {
        return $this->getRepository(AttendanceRecord::class)
            ->findOneBy(
                ['employee' => $empNumber],
                ['id' => ListSorter::DESCENDING]
            );
    }

    /**
     * @param int $empNumber
     * @param DateTime $dateTime
     * @return AttendanceRecord[]
     */
    public function getAttendanceRecordsByEmployeeAndDate(int $empNumber, DateTime $dateTime): array
    {
        $qb = $this->createQueryBuilder(AttendanceRecord::class, 'attendanceRecord');
        $qb->andWhere('attendanceRecord.employee = :empNumber');
        $qb->setParameter('empNumber', $empNumber);
        $qb->andWhere(
            $qb->expr()->between(
                'attendanceRecord.punchInUserTime',
                ':start',
                ':end'
            )
        );
        $qb->setParameter('start', $dateTime->format('Y-m-d') . ' 00:00:00');
        $qb->setParameter('end', $dateTime->format('Y-m-d') . ' 23:59:59');
        return $qb->getQuery()->execute();
    }
}
