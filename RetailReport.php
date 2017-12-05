<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\RetailReport;

class RetailReport extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
        );
    }

    /**
     * Returns required Js Files
     * @param $jsFiles
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/RetailReport/javascripts/retailerReport.js';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/RetailReport/stylesheets/retail.less";
    }

}
