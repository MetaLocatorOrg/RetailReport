<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\RetailReport;
use Piwik\Config;
use Piwik\Common;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Tracker\Action;

/**
 * Archiver that aggregates metrics per retailer_name.
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const RETAILER_NAME_ARCHIVE_RECORD = "RetailReport_conversion";
    const CLICK_ACTION_EVENT_ARCHIVE_RECORD = "RetailReport_click_action";


    protected $maximumRowsInDataTableLevelZero;

    /**
     * @var DataArray $array
     */
    protected $array;

    protected $click_action_array;

    protected $dataTable;

    function __construct($processor)
    {
        parent::__construct($processor);
    }

    /**
     * Archives data for a day period.
     */
    public function aggregateDayReport()
    {
        $this->array = new RetailDataArray();
        $this->click_action_array = new ClickActionDataArray();
        $this->aggregateFromConversions();
        $this->aggregateClickActionEvent();
        $this->insertDayReports();
    }

    /**
     * Insert aggregated daily data serialized
     *
     * @throws \Exception
     */
    protected function insertDayReports()
    {
        /** @var DataTable $dataTable */

        // For conversion table
        $dataTable = $this->array->asDataTable();
        $report = $dataTable->getSerialized();
        $this->getProcessor()->insertBlobRecord(self::RETAILER_NAME_ARCHIVE_RECORD, $report);

        // For click action table
        $click_action_dataTable = $this->click_action_array->asDataTable();
        $click_action_report = $click_action_dataTable->getSerialized();
        $this->getProcessor()->insertBlobRecord(self::CLICK_ACTION_EVENT_ARCHIVE_RECORD, $click_action_report);
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
        // For retail conversion
        $dataTableRecords = array(self::RETAILER_NAME_ARCHIVE_RECORD);
        $columnsAggregationOperation = array(
            'quantity' => 'sum',
            'revenue' => 'sum',
            'unique_purchase' => 'sum'
        );
        $results = $this->getProcessor()->aggregateDataTableRecords(
            $dataTableRecords,
            $maximumRowsInDataTableLevelZero = null,
            $maximumRowsInSubDataTable = null,
            $columnToSortByBeforeTruncation = null,
            $columnsAggregationOperation = $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());

        // For retail click action
        $dataTableRecords = array(self::CLICK_ACTION_EVENT_ARCHIVE_RECORD);
        $columnsAggregationOperation = array(
            'SendToSMS' => 'sum',
            'LocationProduct' => 'sum',
            'GetDirections' => 'sum',
            'ClickToCall' => 'sum'
        );
        $results = $this->getProcessor()->aggregateDataTableRecords(
            $dataTableRecords,
            $maximumRowsInDataTableLevelZero = null,
            $maximumRowsInSubDataTable = null,
            $columnToSortByBeforeTruncation = null,
            $columnsAggregationOperation = $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());
    }

    protected function aggregateFromConversions()
    {
        $select = "
            log_conversion_alias.retailer_name,
            log_action_sku.name AS sku,
            log_action_product_name.name product_name,
            sum(quantity) as quantity,
            quantity * price as revenue,
            count(DISTINCT log_conversion_item.idorder) as unique_purchase
        ";

        $from = array(
            "log_conversion_item",
            array(
                "table" => "log_action",
                "tableAlias" => "log_action_sku",
                "joinOn" => sprintf("log_conversion_item.idaction_sku = log_action_sku.idaction")
            ),
            array(
                "table" => "log_action",
                "tableAlias" => "log_action_product_name",
                "joinOn" => sprintf("log_conversion_item.idaction_name = log_action_product_name.idaction")
            ),
            array(
                "table"      => "log_conversion",
                "joinOn"     => "log_conversion_item.idorder = log_conversion_alias.idorder"
            )
        );

        $where = "log_conversion_item.server_time >= ?
                    AND log_conversion_item.server_time <= ?
                    AND log_conversion_item.idsite = ?
                    AND log_conversion_item.deleted = 0";

        $groupBy = "log_action_sku.name, log_conversion_alias.retailer_name, log_conversion_item.idorder";

        $orderBy = false;
        
        
        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy); 
        $resultSet = $this->getLogAggregator()->getDb()->query($query['sql'], $query['bind']);

        if ($resultSet === false) {
            return;
        }

        while ($row = $resultSet->fetch()) {
            $this->array->sumMetricsRetail($row['retailer_name'], $row);
            $this->array->sumMetricsRetailPivot($row['retailer_name'], $row['sku'], $row);
        }
    }

    protected function getIdFromActionName($actionName, $actionType) 
    {
        $sql = sprintf('SELECT idaction FROM %s WHERE name=? and type=? limit 1', Common::prefixTable('log_action'));
        $result = \Piwik\Db::fetchOne($sql, array($actionName, $actionType));
        return $result;
    }

    /**
     *
     * This function would do sql query to get aggregated number of event action
     * Level one key retailer_name, level_2_key SKU. 
     * Compare the action with log_action and count number of each action for each row.
     */
    protected function aggregateClickActionEvent()
    {
        $click_event_action_id = $this->getIdFromActionName('Click', Action::TYPE_EVENT_ACTION);
        $send_to_sms_event_name_id = $this->getIdFromActionName('SendTo-SMS', Action::TYPE_EVENT_NAME);
        $location_product_event_name_id = $this->getIdFromActionName('Location-Product', Action::TYPE_EVENT_NAME);
        $get_directions_event_name_id = $this->getIdFromActionName('GetDirections', Action::TYPE_EVENT_NAME);
        $click_to_call_event_action_id = $this->getIdFromActionName('Click To Call', Action::TYPE_EVENT_ACTION);
        $select = "
              custom_dimension_3 AS retailer_name,
              custom_dimension_4 AS sku,
              SUM(
                CASE WHEN idaction_event_action = $click_event_action_id and idaction_name = $send_to_sms_event_name_id THEN 1 ELSE 0
              END
            ) AS SendToSMS,

              SUM(
                CASE WHEN idaction_event_action = $click_event_action_id and idaction_name = $location_product_event_name_id THEN 1 ELSE 0
              END
            ) AS LocationProduct,
            SUM(
                CASE WHEN idaction_event_action = $click_event_action_id and idaction_name = $get_directions_event_name_id THEN 1 ELSE 0
              END
            ) AS GetDirections,
            SUM(
                CASE WHEN idaction_event_action = $click_to_call_event_action_id THEN 1 ELSE 0
              END
            ) AS ClickToCall
        ";

        $from = array(
            "log_link_visit_action"
        );

        $where = "log_link_visit_action.server_time >= ?
                    AND log_link_visit_action.server_time <= ?
                    AND log_link_visit_action.idsite = ?";

        $groupBy = "custom_dimension_3, custom_dimension_4";

        $orderBy = false;

        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy); 
        $resultSet = $this->getLogAggregator()->getDb()->query($query['sql'], $query['bind']);

        if ($resultSet === false) {
            return;
        }

        while ($row = $resultSet->fetch()) {
            $this->click_action_array->sumMetricsClickAction($row['retailer_name'], $row);
            $this->click_action_array->sumMetricsClickActionPivot($row['retailer_name'], $row['sku'], $row);
        }
    }
}
