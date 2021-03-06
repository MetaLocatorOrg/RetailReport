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
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;

use Piwik\Plugins\RetailReport\Model;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetUniqueActions extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('RetailReport_UniqueActions');
        $this->categoryId = 'General_Actions';
        $this->dimension     = null;
        $this->documentation = Piwik::translate('');

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 1;

        // By default standard metrics are defined but you can customize them by defining an array of metric names
        $this->metrics       = array('uniq_action_visits', 'action_visits', 'unique_actions_regions', 'region_name');

        // Uncomment the next line if your report does not contain any processed metrics, otherwise default
        // processed metrics will be assigned
        // $this->processedMetrics = array();

        // Uncomment the next line if your report defines goal metrics
        // $this->hasGoalMetrics = true;

        // Uncomment the next line if your report should be able to load subtables. You can define any action here
        // $this->actionToLoadSubTables = $this->action;

        // If a subcategory is specified, the report will be displayed in the menu under this menu item
        $this->subcategoryId = 'RetailReport_UniqueActions';
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
            //$view->config->addTranslations(array('label' => $this->dimension->getName()));
        }

        // $view->config->show_search = false;
        // $view->requestConfig->filter_sort_column = 'nb_visits';
        // $view->requestConfig->filter_limit = 10';

        $view->config->columns_to_display = array_merge($this->metrics);
        $view->config->datatable_js_type   = 'RetailerUniqueAction';
        $view->config->datatable_css_class = 'RetailerUniqueAction';

        $action_list_view = new View('@RetailReport/action_list');
        
        $model = new Model();
        $action_list = $model->getActionList();
       
        /*
        $action_list = array(
            array('value'=> 43, 'name'=>'Total Search'),
            array('value' => 115, 'name' =>  'Keywork Search') 
        );
         */
        $new_action_list = array();
        $current_unique_action_id = Common::getRequestVar('unique_action_id', 43, 'int');
        $view->requestConfig->request_parameters_to_modify['unique_action_id'] = $current_unique_action_id;
        foreach ($action_list as $action) {
            if ($action['value'] == $current_unique_action_id) {
                $action['selected'] = 'selected';
            } else {
                $action['selected'] = '';
            }
                $new_action_list[] = $action;
        }
        $action_list_view->action_list = $new_action_list;

        $view->config->show_footer_message = $action_list_view->render();
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
        $view = new View('@RetailReport/getUniqueActions');
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
