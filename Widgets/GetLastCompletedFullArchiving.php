<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\RetailReport\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;
use Piwik\Http;
use Piwik\Option;
use Piwik\Date;

/**
 * This class allows you to add your own widget to the Piwik platform. In case you want to remove widgets from another
 * plugin please have a look at the "configureWidgetsList()" method.
 * To configure a widget simply call the corresponding methods as described in the API-Reference:
 * http://developer.piwik.org/api-reference/Piwik/Plugin\Widget
 */
class GetLastCompletedFullArchiving extends Widget
{
    const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";
    public static function configure(WidgetConfig $config)
    {
        /**
         * Set the category the widget belongs to. You can reuse any existing widget category or define
         * your own category.
         */
        $config->setCategoryId('RetailReport_Info');

        /**
         * Set the subcategory the widget belongs to. If a subcategory is set, the widget will be shown in the UI.
         */
        // $config->setSubcategoryId('General_Overview');

        /**
         * Set the name of the widget belongs to.
         */
        $config->setName('RetailReport_LastCompletedFullArchiving');

        /**
         * Set the order of the widget. The lower the number, the earlier the widget will be listed within a category.
         */
        $config->setOrder(99);

        /**
         * Optionally set URL parameters that will be used when this widget is requested.
         * $config->setParameters(array('myparam' => 'myvalue'));
         */

        /**
         * Define whether a widget is enabled or not. For instance some widgets might not be available to every user or
         * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
         * set `true` or `false`. If your widget is only available to users having super user access you can do the
         * following:
         *
         * $config->setIsEnabled(\Piwik\Piwik::hasUserSuperUserAccess());
         * or
         * if (!\Piwik\Piwik::hasUserSuperUserAccess())
         *     $config->disable();
         */
    }

    protected function readSetting()
    {
        $settings = new \Piwik\Plugins\RetailReport\SystemSettings();
        $this->itemid = $settings->itemid->getValue();
        $this->apikey = $settings->apikey->getValue();
        $this->username = $settings->username->getValue();
        $this->password = $settings->password->getValue();
        $this->baseurl = $settings->baseurl->getValue();
    }

    /**
     * This method renders a widget as defined in "init()". It's on you how to generate the content of the
     * widget. As long as you return a string everything is fine. You can use for instance a "Piwik\View" to render a
     * twig template. In such a case don't forget to create a twig template (eg. myViewTemplate.twig) in the
     * "templates" directory of your plugin.
     *
     * @return string
     */
    public function render()
    {
        $view = new View('@RetailReport/latestConversionBackfill');
        $view->latestAPIUpdate = $this->getLatestAPIUpdate();
        $view->latestArchive = $this->getLatestArchive();
        return $view->render();
    }

    public function getLatestArchive()
    {
        $latest_timestamp = Option::get(self::OPTION_ARCHIVING_FINISHED_TS);
        $secondsBackToLookForVisits = time() - $latest_timestamp;

        return Date::now()->subSeconds($secondsBackToLookForVisits)->getDatetime() . ' UTC';
    }

    public function getLatestAPIUpdate()
    {
        #Piwik::checkUserHasSuperUserAccess();
        $this->readSetting();

        $timeout = 5;
        $parameters = array(
            'Itemid' => $this->itemid,
            'apikey' => $this->apikey,
            'username'=> $this->username,
            'password' => $this->password
        );
        $url = $this->baseurl . '?' . urldecode(http_build_query($parameters, '', '&'));

        try {
            $response = Http::sendHttpRequestBy(
                Http::getTransportMethod(),
                $url,
                $timeout,
                $acceptInvalidSslCertificate = true,
                $httpMethod = 'GET'
            );
            $datetime = date_parse($response);
            $date = $datetime['year'] . '-' . $datetime['month'] . '-'. $datetime['day'];
        } catch (Exception $e) {
            $result = self::ERROR_STRING . " " . $e->getMessage();
            return '';
        }
        
        return $date;
    }

}
