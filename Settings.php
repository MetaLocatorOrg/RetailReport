<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\RetailReport;

use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;

/**
 * Defines Settings for RetailReport.
 *
 * Usage like this:
 * $settings = new Settings('RetailReport');
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class Settings extends \Piwik\Plugin\Settings
{

    public $itemid;
    public $apikey;
    public $username;
    public $password;
    public $baseurl;

    protected function init()
    {
        $this->setIntroduction('Plugin for Fatica Retailer Report');

        $this->createItemid();
        $this->createApikey();
        $this->createUsername();
        $this->createPassword();
        $this->createBaseurl();
    }

    private function createBaseurl()
    {
        $this->baseurl = new SystemSetting('fatica_baseurl', 'Base url');
        $this->baseurl->readableByCurrentUser = true;
        $this->baseurl->uiControlType = static::CONTROL_TEXT;
        $this->baseurl->description   = 'Base url';
        $this->baseurl->defaultValue  = "https://admin.metalocator.com/webapi/api/getMostRecentTransactionTimeImportedToPiwik";
        $this->addSetting($this->baseurl);
    }

    private function createItemid()
    {
        $this->itemid = new SystemSetting('fatica_itemid', 'Item id');
        $this->itemid->readableByCurrentUser = true;
        $this->itemid->uiControlType = static::CONTROL_TEXT;
        $this->itemid->description   = 'Item id';
        $this->itemid->defaultValue  = "";
        $this->addSetting($this->itemid);
    }

    private function createApikey()
    {
        $this->apikey = new SystemSetting('fatica_apikey', 'API key');
        $this->apikey->readableByCurrentUser = true;
        $this->apikey->uiControlType = static::CONTROL_TEXT;
        $this->apikey->description   = 'API key';
        $this->apikey->defaultValue  = "";

        $this->addSetting($this->apikey);
    }

    private function createUsername()
    {
        $this->username = new SystemSetting('fatica_username', 'API username');
        $this->username->readableByCurrentUser = true;
        $this->username->uiControlType = static::CONTROL_TEXT;
        $this->username->description   = 'username for the API';
        $this->username->defaultValue  = "";

        $this->addSetting($this->username);
    }

    private function createPassword()
    {
        $this->password = new SystemSetting('fatica_password', 'API password');
        $this->password->readableByCurrentUser = true;
        $this->password->uiControlType = static::CONTROL_PASSWORD;
        $this->password->description   = 'Password for the 3rd API where we fetch the value';
        $this->addSetting($this->password);
    }
}
