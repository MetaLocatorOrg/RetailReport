<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\RetailReport;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Tracker;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

/**
 * The SegmentEditor Model lets you persist and read custom Segments from the backend without handling any logic.
 */
class Model
{
    private static $log_conversion_item = 'log_conversion_item';

    protected function getConversionItemTable()
    {
        return Common::prefixTable(self::$log_conversion_item);
    }

    public functioN getIdActions($oldProductName, $newProductName)
    {
        $actionsToLookup = array();
        $actionsToLookup[] = array(trim($oldProductName), Action::TYPE_ECOMMERCE_ITEM_NAME);
        $actionsToLookup[] = array(trim($newProductName), Action::TYPE_ECOMMERCE_ITEM_NAME);
        $idActions = TableLogAction::loadIdsAction($actionsToLookup);
        return $idActions;
    }

    public function getConversionItemName($oldLogActionId)
    {
        $select = "SELECT idsite, server_time";
        $from = "FROM " . $this->getConversionItemTable();
        $where = "idaction_name = ?";
        $sql = "$select $from WHERE " . $where;
        $bindSql = array($oldLogActionId);
        $conversion_items = $this->getDb()->fetchAll($sql, $bindSql);
        return $conversion_items;
    }

    public function updateConversionItemName($oldLogActionId, $newLogActionId) 
    {
        $table = $this->getConversionItemTable();
        $parts = 'idaction_name = ?';
        $sql = "UPDATE $table SET $parts WHERE idaction_name = ?";
        $sqlBind[] = $newLogActionId;
        $sqlBind[] = $oldLogActionId;
        try {
            $this->getDb()->query($sql, $sqlBind);
        } catch (Exception $e) {
            Common::printDebug("There was an error while updating the Conversion: " . $e->getMessage());
            return false;
        }
        return true;
    }

    private function getDb()
    {
        return Tracker::getDatabase();
    }
}
