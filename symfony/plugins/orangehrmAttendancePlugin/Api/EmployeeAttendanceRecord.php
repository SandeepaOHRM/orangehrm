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

namespace OrangeHRM\Attendance\Api;

use OrangeHRM\Attendance\Traits\Service\AttendanceServiceTrait;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Entity\AttendanceRecord;

class EmployeeAttendanceRecord extends Endpoint implements CrudEndpoint
{
    use AttendanceServiceTrait;

    public const PARAMETER_DATE = 'date';
    public const PARAMETER_TIME = 'time';
    public const PARAMETER_TIMEZONE_OFFSET = 'timezoneOffset';
    public const PARAMETER_NOTE = 'note';

    private ?bool $canUserChangeCurrentTime = null;
    private ?bool $canUserModifyAttendance = null;
    private ?bool $canSupervisorModifyAttendance = null;

    /**
     * @return bool
     */
    public function canUserChangeCurrentTime(): bool
    {
        if (is_null($this->canUserChangeCurrentTime)) {
            $this->getAttendanceService()->canUserChangeCurrentTimeConfiguration();
        }
        return $this->canUserChangeCurrentTime;
    }

    /**
     * @return bool
     */
    public function canUserModifyAttendance(): bool
    {
        if (is_null($this->canUserModifyAttendance)) {
            $this->getAttendanceService()->canUserModifyAttendanceConfiguration();
        }
        return $this->canUserModifyAttendance;
    }

    /**
     * @return bool
     */
    public function canSupervisorModifyAttendance(): bool
    {
        if (is_null($this->canSupervisorModifyAttendance)) {
            $this->getAttendanceService()->canSupervisorModifyAttendanceConfiguration();
        }
        return $this->canSupervisorModifyAttendance;
    }

    /**
     * @return bool|null
     */
    protected function getLastAttendanceRecord(): ?bool
    {
        //ToDo
        return true;
    }

    /**
     * @return int
     */
    protected function getEmpNumber(): int
    {
        return $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );
    }

    /**
     * @return ParamRule
     */
    private function getEmpNumberParamRule(): ParamRule
    {
        return new ParamRule(CommonParams::PARAMETER_EMP_NUMBER, new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS));
    }

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        $attendanceRecord = new AttendanceRecord();
        $attendanceRecord->getDecorator()->setEmployeeByEmpNumber($this->getEmpNumber());

        $date = $this->getRequestParams()->getDateTime(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_DATE
        );
        $time = $this->getRequestParams()->getDateTime(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_TIME
        );
        $timezoneOffset = $this->getRequestParams()->getFloat(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_TIMEZONE_OFFSET
        );
        $note = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_NOTE
        );

        if(!$this->canUserChangeCurrentTime()){

        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getEmpNumberParamRule(),
            ...$this->getCommonBodyValidationRules()
        );
    }

    protected function getCommonBodyValidationRules(): array
    {
        return [
            new ParamRule(
                self::PARAMETER_DATE,
                new Rule(Rules::API_DATE)
            ),
            new ParamRule(
                self::PARAMETER_TIME,
                new Rule(Rules::TIME)
            ),
            new ParamRule(
                self::PARAMETER_TIMEZONE_OFFSET,
                new Rule(Rules::DECIMAL)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_NOTE,
                    new Rule(Rules::STRING_TYPE),
                )
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}
