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

namespace OrangeHRM\LDAP\Api;

use OrangeHRM\Core\Api\V2\CollectionEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Core\Traits\ValidatorTrait;
use OrangeHRM\LDAP\Api\Traits\LDAPDataMapParamRuleCollection;
use OrangeHRM\LDAP\Dto\LDAPSetting;

class LDAPConfigAPI extends Endpoint implements CollectionEndpoint
{
    use ConfigServiceTrait;
    use ValidatorTrait;
    use LDAPDataMapParamRuleCollection;

    public const PARAMETER_ENABLED = 'enabled';
    public const PARAMETER_HOSTNAME = 'hostname';
    public const PARAMETER_PORT = 'port';
    public const PARAMETER_ENCRYPTION = 'encryption';
    public const PARAMETER_PROTOCOL = 'protocol';
    public const PARAMETER_LDAP_IMPLEMENTATION = 'ldapImplementation';

    public const PARAMETER_BIND_ANONYMOUSLY = 'bindAnonymously';
    public const PARAMETER_DISTINGUISHED_NAME = 'distinguishedName';
    public const PARAMETER_DISTINGUISHED_PASSWORD = 'distinguishedPassword';
    public const PARAMETER_BASE_DISTINGUISHED_NAME = 'baseDistinguishedName';
    public const PARAMETER_SEARCH_SCOPE = 'searchScope';
    public const PARAMETER_USER_NAME_ATTRIBUTE = 'userNameAttribute';

    public const PARAMETER_DATA_MAPPING = 'dataMapping';
    public const PARAMETER_FIRST_NAME = 'firstName';
    public const PARAMETER_LAST_NAME = 'lastName';
    public const PARAMETER_USER_STATUS = 'userStatus';
    public const PARAMETER_WORK_EMAIL = 'workEmail';
    public const PARAMETER_EMPLOYEE_ID = 'employeeId';

    public const PARAMETER_GROUP_OBJECT_CLASS = 'groupObjectClass';
    public const PARAMETER_GROUP_OBJECT_FILTER = 'groupObjectFilter';
    public const PARAMETER_GROUP_NAME_ATTRIBUTE = 'groupNameAttribute';
    public const PARAMETER_GROUP_MEMBERS_ATTRIBUTE = 'groupMembersAttribute';
    public const PARAMETER_GROUP_MEMBERSHIP_ATTRIBUTE = 'groupMembershipAttribute';
    public const PARAMETER_SYNC_INTERVAL = 'syncInterval';

    public const PARAM_RULE_ATTRIBUTE_MAX_LENGTH = 100;


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
//        $dataMapping = $this->getRequestParams()->getArray(
//            RequestParams::PARAM_TYPE_BODY,
//            self::PARAMETER_DATA_MAPPING
//        );
//        $this->validate($dataMapping, $this->getParamRuleCollection());

        $LDAPSettings = new LDAPSetting(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_HOSTNAME
            ),
            $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_PORT
            ),
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_LDAP_IMPLEMENTATION
            ),
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_ENCRYPTION
            ),
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_BASE_DISTINGUISHED_NAME
            ),
        );

        $this->setConfigAttributes($LDAPSettings);

        $this->getConfigService()->getConfigDao()->setValue(
            ConfigService::KEY_LDAP_SETTINGS,
            (string)$LDAPSettings
        );
        return new EndpointResourceResult(ArrayModel::class, $LDAPSettings);
    }

    /**
     * @param LDAPSetting $LDAPSetting
     */
    private function setConfigAttributes(LDAPSetting $LDAPSetting): void
    {
        $bindAnonymously = $this->getRequestParams()->getBoolean(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_BIND_ANONYMOUSLY
        );
        $LDAPSetting->setBindAnonymously($bindAnonymously);
        if (!$bindAnonymously) {
            $LDAPSetting->setBindUserDN(
                $this->getRequestParams()->getString(
                    RequestParams::PARAM_TYPE_BODY,
                    self::PARAMETER_DISTINGUISHED_NAME
                )
            );
            $LDAPSetting->setBindUserPassword(
                $this->getRequestParams()->getString(
                    RequestParams::PARAM_TYPE_BODY,
                    self::PARAMETER_DISTINGUISHED_PASSWORD
                )
            );
        }
        $LDAPSetting->setBaseDN(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_BASE_DISTINGUISHED_NAME
            )
        );
        $LDAPSetting->setSearchScope(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_SEARCH_SCOPE
            )
        );
        $LDAPSetting->setFirstName(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_FIRST_NAME
            )
        );
        $LDAPSetting->setLastName(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_LAST_NAME
            )
        );
        $LDAPSetting->setUserStatus(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_USER_STATUS
            )
        );
        $LDAPSetting->setWorkEmail(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_WORK_EMAIL
            )
        );
        $LDAPSetting->setEmployeeId(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_EMPLOYEE_ID
            )
        );
        $LDAPSetting->setGroupObjectClass(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_GROUP_OBJECT_CLASS
            )
        );
        $LDAPSetting->setGroupObjectFilter(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_GROUP_OBJECT_FILTER
            )
        );
        $LDAPSetting->setGroupNameAttribute(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_GROUP_NAME_ATTRIBUTE
            )
        );
        $LDAPSetting->setGroupMembersAttribute(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_GROUP_MEMBERS_ATTRIBUTE
            )
        );
        $LDAPSetting->setGroupMembershipAttribute(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_GROUP_MEMBERSHIP_ATTRIBUTE
            )
        );
        $LDAPSetting->setSyncInterval(
            $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_SYNC_INTERVAL
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_ENABLED,
                new Rule(Rules::BOOL_TYPE)
            ),
            new ParamRule(
                self::PARAMETER_HOSTNAME,
                new Rule(Rules::BOOL_TYPE)
            ),
            new ParamRule(
                self::PARAMETER_PORT,
                new Rule(Rules::NUMBER)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ENCRYPTION,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
                )
            ),
            new ParamRule(
                self::PARAMETER_PROTOCOL,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_LDAP_IMPLEMENTATION,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_BIND_ANONYMOUSLY,
                new Rule(Rules::BOOL_TYPE)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_DISTINGUISHED_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_DISTINGUISHED_PASSWORD,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_BASE_DISTINGUISHED_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
                )
            ),
            new ParamRule(
                self::PARAMETER_SEARCH_SCOPE,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_USER_NAME_ATTRIBUTE,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_GROUP_OBJECT_CLASS,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_GROUP_OBJECT_FILTER,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_GROUP_NAME_ATTRIBUTE,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_GROUP_MEMBERS_ATTRIBUTE,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_GROUP_MEMBERSHIP_ATTRIBUTE,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [0, self::PARAM_RULE_ATTRIBUTE_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_SYNC_INTERVAL,
                new Rule(Rules::NUMBER),
            ),
            new ParamRule(
                self::PARAMETER_DATA_MAPPING,
                new Rule(Rules::ARRAY_TYPE)
            )
        );
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
}
