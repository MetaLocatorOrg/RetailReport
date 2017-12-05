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
    public function getSKUfromRetailer($idSite, $period, $date, $segment = false, $idSubtable = null)
    {
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        return $dataTable;
    }
}
