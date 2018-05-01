<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\RetailReport;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for RetailReport.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    public $itemid;
    public $apikey;
    public $username;
    public $password;
    public $baseurl;

    protected function init()
    {
        $this->itemid = $this->createItemIdSetting();
        $this->apikey = $this->createApiKeySetting();
        $this->baseurl = $this->createBaseUrlSetting();
        $this->username = $this->createUsernameSetting();
        $this->password = $this->createPasswordSetting();
    }

    private function createItemIdSetting()
    {
        $default = '';
        return $this->makeSetting('fatica_itemid', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Item Id';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Item id';
        });
    }

    private function createBaseUrlSetting()
    {
        $default = 'https://admin.metalocator.com/webapi/api/getMostRecentTransactionTimeImportedToPiwik';
        return $this->makeSetting('fatica_baseurl', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Base url';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Base url';
        });
    }

    private function createApiKeySetting()
    {
        $default = '';
        return $this->makeSetting('fatica_apikey', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Api Key';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Api Key';
        });
    }

    private function createUsernameSetting()
    {
        $default = '';
        return $this->makeSetting('fatica_username', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'API username';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'username for api';
        });
    }

    private function createPasswordSetting()
    {
        return $this->makeSetting('fatica_password', $default = null, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'API password';
            $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            $field->description = 'Password for the 3rd API where we fetch the value';
        });
    }
}
