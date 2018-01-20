<?php
namespace Piwik\Plugins\RetailReport;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Date;

class Model
{

    public function getUniqueActionByAction($event_action_id, $dateStart, $dateEnd)
    {
        $db = $this->getDb();
        $start_time = $dateStart->toString(Date::DATE_TIME_FORMAT);
        $end_time = $dateEnd->toString(Date::DATE_TIME_FORMAT);
        $bind = array(
          1228,
          $event_action_id,
          $start_time,
          $end_time);

        $resultSet = $db->query(
            "SELECT
                count(DISTINCT idvisit) uniq_action_visits,
                count(idvisit) action_visits,
                count(DISTINCT idvisit,idaction_name) unique_actions_regions,
                a.custom_dimension_7 region_name
            FROM
                " . Common::prefixTable('log_link_visit_action') . " a
            WHERE
                idsite = ?
                AND idaction_event_action = ?
                AND a.server_time >= ? 
                AND a.server_time <= ? 
            GROUP BY
                a.custom_dimension_7
            ORDER BY
                count(DISTINCT idvisit,idaction_name)
            DESC", $bind);
        $actions = [];

        if ($resultSet === false) {
            return;
        }

        while ($row = $resultSet->fetch()) {
            $actions[] = array(
                'uniq_action_visits' => $row['uniq_action_visits'],
                'action_visits' => $row['action_visits'],
                'unique_actions_regions' => $row['unique_actions_regions'],
                'region_name' => $row['region_name']
            );
        }
        return $actions;
    }

    private function getDb()
    {
        return Db::get();
    }
}
