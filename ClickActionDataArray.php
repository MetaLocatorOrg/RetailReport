<?php
namespace Piwik\Plugins\RetailReport;
use Piwik\DataArray;

class ClickActionDataArray extends \Piwik\DataArray
{
    protected function doSumClickActionMetrics($newRowToAdd, &$oldRowToUpdate)
    {
        $oldRowToUpdate['SendToSMS'] += $newRowToAdd['SendToSMS'];
        $oldRowToUpdate['LocationProduct'] += $newRowToAdd['LocationProduct'];
        $oldRowToUpdate['GetDirections'] += $newRowToAdd['GetDirections'];
        $oldRowToUpdate['ClickToCall'] += $newRowToAdd['ClickToCall'];
        return;
    }

    public function sumMetricsClickAction($label, $row)
    {
        if (!isset($this->data[$label])) {
            $this->data[$label] = static::makeEmptyClickActionRow();
        }
        $row['product_name'] = '';
        $this->doSumClickActionMetrics($row, $this->data[$label]);
    }

    public function sumMetricsClickActionPivot($parentLabel, $label, $row)
    {
        if (!isset($this->dataTwoLevels[$parentLabel][$label])) {
            $this->dataTwoLevels[$parentLabel][$label] = static::makeEmptyClickActionRow();
        }
        $this->doSumClickActionMetrics($row, $this->dataTwoLevels[$parentLabel][$label]);
    }

    protected static function makeEmptyClickActionRow()
    {
        return array(
            'SendToSMS'       => 0,
            'LocationProduct' => 0,
            'GetDirections'   => 0,
            'ClickToCall'     => 0,
        );
    }
}

