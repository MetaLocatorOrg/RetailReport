<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\RetailReport;

use Piwik\DataTable;
use Piwik\Archive;
use Piwik\API\Request;
use Piwik\DataTable\Row;
use Piwik\Site;
use Piwik\Date;
use Piwik\Period;

/**
 * API for plugin RetailReport
 *
 * @method static \Piwik\Plugins\RetailReport\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param string $segment
     * @param bool $expanded
     * @param int $idSubtable
     *
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable($idSite, $period, $date, $segment, $expanded, $flat, $idSubtable)
    {
        $dataTable = Archive::createDataTableFromArchive(
            Archiver::RETAILER_NAME_ARCHIVE_RECORD, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        return $dataTable;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getRetailReport($idSite, $period, $date, $segment=false, $expanded=false, $flat=false, $idSubtable=null)
    {
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded, $flat, $idSubtable = null);
        return $dataTable;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getSKUfromRetailer($idSite, $period, $date, $segment=false, $expanded=false, $flat=false, $idSubtable = null)
    {
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        return $dataTable;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getActionsbyRetailer($idSite, $period, $date, $segment=false, $expanded=false, $flat=false, $idSubtable=null)
    {
        $dataTable = Archive::createDataTableFromArchive(
            Archiver::CLICK_ACTION_EVENT_ARCHIVE_RECORD, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable=null);
        return $dataTable;
    }


    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getActionsbySKURetailer($idSite, $period, $date, $segment = false, $expanded=false, $flat=false, $idSubtable = null)
    {
        $dataTable = Archive::createDataTableFromArchive(
            Archiver::CLICK_ACTION_EVENT_ARCHIVE_RECORD, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        return $dataTable;
    }

    private function getModel()
    {
        return new Model();
    }

    public function updateProductName($oldProductName, $newProductName)
    {
        $model = $this->getModel();
        $idActions = $model->getIdActions($oldProductName, $newProductName);
        $oldLogActionId = $idActions[0];
        $newLogActionId = $idActions[1];
        $conversion_items = $model->getConversionItemName($oldLogActionId);
        $invalidData = array();
        foreach ($conversion_items as $item) {
            $idSite = (int)$item['idsite'];
            if (!array_key_exists($idSite, $invalidData)) {
                $invalidData[$idSite] = [];
            }
            $timezone = Site::getTimezoneFor($idSite);
            $server_time = Date::factory($item['server_time'], $timezone)->toString('Y-m-d');
            $invalidData[$idSite][$server_time] = 1;
        }
        $result = $model->updateConversionItemName($oldLogActionId, $newLogActionId);

        foreach ($invalidData as $siteId => $date_arrays) {
            $dates = array_keys($date_arrays);
            Request::processRequest('CoreAdminHome.invalidateArchivedReports', [
                'format'  => 'json',
                'idSites' => $siteId,
                'period'  => false,
                'dates'   => implode(',', $dates),
            ]);

        }
        return $result;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getUniqueActions($idSite, $period, $date, $segment = false, $unique_action_id = false)
    {
        $table = new DataTable();
        $p = \Piwik\Period\Factory::build($period, $date);

        $actions = [];
        if ($idSite == 1228) {
            $dateStart = $p->getDateTimeStart();
            $dateEnd = $p->getDateTimeEnd();
            $model = new Model();
            if (!$unique_action_id) {
              $unique_action_id = 43;
            }
            $actions = $model->getUniqueActionByAction($unique_action_id, $dateStart, $dateEnd);
        }
        $table = DataTable::makeFromSimpleArray($actions);
        return $table;
    }

}
