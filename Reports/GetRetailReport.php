<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\RetailReport\Reports;

use Piwik\Piwik;
use Piwik\Common;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\RetailReport\Columns\Retailer;
use Piwik\View;
use Piwik\Plugins\RetailReport\Columns\Metrics\AveragePrice;
use Piwik\Plugins\RetailReport\Columns\Metrics\AverageQuantity;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\API\Request;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetRetailReport extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('RetailReport_RetailReport');
        $this->dimension     = new Retailer();
        $this->documentation = Piwik::translate('');

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 1;

        // By default standard metrics are defined but you can customize them by defining an array of metric names
        $this->metrics = array(
            'revenue', 'quantity', 'unique_purchase', 'avg_price', 'avg_quantity', 'conversion_rate', 'ctr'
        );

        // Uncomment the next line if your report does not contain any processed metrics, otherwise default
        // processed metrics will be assigned
        $this->processedMetrics = array(
            new AveragePrice(),
            new AverageQuantity()
        );

        // Uncomment the next line if your report should be able to load subtables. You can define any action here
        $this->actionToLoadSubTables = 'getSKUfromRetailer';

        // Uncomment the next line if your report always returns a constant count of rows, for instance always
        // 24 rows for 1-24hours
        // $this->constantRowsCount = true;

        // If a menu title is specified, the report will be displayed in the menu
        $this->menuTitle    = 'RetailReport_RetailReport';

        // If a widget title is specified, the report will be displayed in the list of widgets and the report can be
        // exported as a widget
        $this->widgetTitle  = 'RetailReport_RetailReport';
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        if (!empty($this->dimension)) {
            $view->config->addTranslations(array(
                'label' => $this->dimension->getName(),
                'unique_purchase' => 'Unique purchase',
                'product_name' => 'Product Name'
            ));
        }

        $idSite = Common::getRequestVar('idSite');
        $period = Common::getRequestVar('period');
        $date   = Common::getRequestVar('date');
        $segment= Common::getRequestVar('segment', '', 'string');
        /*
        if (!($view instanceof Evolution)) {
            $moneyColumns = array('revenue');
            $callback = function ($revenue) {
                $formatter    = new Formatter();
                $idSite = Common::getRequestVar('idSite');
                return $formatter->getPrettyMoney($revenue, $idSite);
            };

            //$view->config->filters[] = array('ColumnCallbackReplace', array($moneyColumns, $formatter, array($idSite)));
        }*/


        $conversion_callback = function ($unique_purchase) use ($idSite, $period, $date, $segment)
        {
            $visits = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, $segment);
            $firstRow = $visits->getFirstRow();
            $totalValue = 0;
            if (!empty($firstRow)) {
                $totalValue = $firstRow->getColumn('nb_visits');
            }
            if ($totalValue== 0) {
                return '0%';
            } else {
                return round(100 * $unique_purchase / $totalValue, 2) . '%';
            }
        };

        $ctr_callback = function ($label) use ($idSite, $period, $date, $segment)
        {
            if ($segment != '') {
                $content_segment = 'contentName==' . $label . ';' . $segment;
            } else {
                $content_segment = 'contentName==' . $label;
            }
            $result = Request::processRequest("Contents.getContentNames", array(
                'segment' => $content_segment
            ));
            $firstRow = $result->getFirstRow();
            $nb_interactions = 0;
            if (!empty($firstRow)) {
                $nb_interactions = $firstRow->getColumn('nb_interactions');
            }

            $visits = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, $segment);
            $firstRow = $visits->getFirstRow();
            $totalValue = 0;
            if (!empty($firstRow)) {
                $totalValue = $firstRow->getColumn('nb_visits');
            }

            if ($totalValue== 0) {
                return '0%';
            } else {
                return round(100 * $nb_interactions / $totalValue, 2) . '%';
            }
        };
 
        $view->config->filters[] = array('ColumnCallbackAddColumn', array(array('unique_purchase'), 'conversion_rate', $conversion_callback));
        $view->config->filters[] = array('ColumnCallbackAddColumn', array(array('label'), 'ctr', $ctr_callback));

        // $view->config->show_search = false;
        // $view->requestConfig->filter_sort_column = 'nb_visits';
        // $view->requestConfig->filter_limit = 10';
        $view->config->show_table_all_columns = false;
        $view->config->show_insights = false;
        $view->config->show_active_view_icon = false;
        $view->config->show_related_reports = false;
        $view->config->show_pivot_by_subtable = false;
        $view->config->show_flatten_table = false;
        $view->config->show_bar_chart = false;
        $view->config->show_pie_chart = false;
        $view->config->show_tag_cloud = false;
        
        $view->config->columns_to_display = array_merge(array('label'), $this->metrics);

    }

    /**
     * Here you can define related reports that will be shown below the reports. Just return an array of related
     * report instances if there are any.
     *
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
        return array(); // eg return array(new XyzReport());
    }

    /**
     * A report is usually completely automatically rendered for you but you can render the report completely
     * customized if you wish. Just overwrite the method and make sure to return a string containing the content of the
     * report. Don't forget to create the defined twig template within the templates folder of your plugin in order to
     * make it work. Usually you should NOT have to overwrite this render method.
     *
     * @return string
    public function render()
    {
        $view = new View('@RetailReport/getRetailReport');
        $view->myData = array();

        return $view->render();
    }
    */

    /**
     * By default your report is available to all users having at least view access. If you do not want this, you can
     * limit the audience by overwriting this method.
     *
     * @return bool
    public function isEnabled()
    {
        return Piwik::hasUserSuperUserAccess()
    }
     */
}
