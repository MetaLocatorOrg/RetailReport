<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\RetailReport;
use Piwik\Config;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;

/**
 * Archiver that aggregates metrics per retailer_name.
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const RETAILER_NAME_ARCHIVE_RECORD = "RetailReport_conversion";
    const CLICK_TO_CALL_EVENT_ARCHIVE_RECORD = "RetailReport_click_to_call";
    const SEND_SMS_EVENT_ARCHIVE_RECORD = "RetailReport_send_sms";
    const GET_DIRECTION_EVENT_ARCHIVE_RECORD = "RetailReport_get_direction";
    const CLICK_PRODUCT_EVENT_ARCHIVE_RECORD = "RetailReport_click_product";


    protected $maximumRowsInDataTableLevelZero;

    /**
     * @var DataArray $array
     */
    protected $array;

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
        $this->aggregateFromConversions();
        //$this->aggregateClickToCallEvent();
        //$this->aggregateSendSmsEvent();
        //$this->aggregateGetDirectionEvent();
        //$this->aggregateClickProductEvent();
        $this->insertDayReports();

    }
    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
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

    /**
     * Insert aggregated daily data serialized
     *
     * @throws \Exception
     */
    protected function insertDayReports()
    {
        /** @var DataTable $dataTable */

        $dataTable = $this->array->asDataTable();
        $report = $dataTable->getSerialized();
        $this->getProcessor()->insertBlobRecord(self::RETAILER_NAME_ARCHIVE_RECORD, $report);
    }

    /**
     *
     * This function would do sql query to get aggregated number of event action
     * Level one key retailer_name, level_2_key SKU. 
     * Compare the action with log_action and count number of each action for each row.
     */
    protected function aggregateRetailAction()
    {
        $select = "
            log_conversion.retailer_name,
            log_action_sku.name AS sku,
            log_action_product_name.name product_name,
            sum(quantity) as quantity,
            quantity * price as revenue,
            count(DISTINCT log_conversion_item.idorder) as unique_purchase
        ";

        $from = array(
            "log_link_visit_action",
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
                "joinOn"     => "log_conversion_item.idorder = log_conversion.idorder"
            )
        );

        $where = "log_conversion_item.server_time >= ?
                    AND log_conversion_item.server_time <= ?
                    AND log_conversion_item.idsite = ?
                    AND log_conversion_item.deleted = 0";

        $groupBy = "log_action_sku.name, log_conversion.retailer_name, log_conversion_item.idorder";

        $orderBy = false;
    }
}
