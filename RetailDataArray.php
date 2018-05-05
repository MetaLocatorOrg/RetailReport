<?php
namespace Piwik\Plugins\RetailReport;
use Piwik\DataArray;

class RetailDataArray extends \Piwik\DataArray
{
    protected function doSumRetailMetrics($newRowToAdd, &$oldRowToUpdate)
    {
        $oldRowToUpdate['quantity'] += $newRowToAdd['quantity'];
        $oldRowToUpdate['revenue'] += $newRowToAdd['revenue'];
        $oldRowToUpdate['unique_purchase'] += $newRowToAdd['unique_purchase'];
        $oldRowToUpdate['product_name'] = $newRowToAdd['product_name'];
        return;
    }

    public function sumMetricsRetail($label, $row)
    {
        if (!isset($this->data[$label])) {
            $this->data[$label] = static::makeEmptyRetailRow();
        }
        $row['product_name'] = '';
        $this->doSumRetailMetrics($row, $this->data[$label]);
    }

    public function sumMetricsRetailPivot($parentLabel, $label, $row)
    {
        if (!isset($this->dataTwoLevels[$parentLabel][$label])) {
            $this->dataTwoLevels[$parentLabel][$label] = static::makeEmptyRetailRow();
        }
        $this->doSumRetailMetrics($row, $this->dataTwoLevels[$parentLabel][$label]);
    }

    protected static function makeEmptyRetailRow()
    {
        return array(
            'product_name'    => '',
            'quantity'        => 0,
            'revenue'         => 0,
            'unique_purchase' => 0,
        );
    }
}
