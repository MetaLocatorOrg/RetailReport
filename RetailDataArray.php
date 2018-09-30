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
        $oldRowToUpdate['campaign_name'] = $newRowToAdd['campaign_name'];
        $oldRowToUpdate['campaign_content'] = $newRowToAdd['campaign_content'];
        $oldRowToUpdate['campaign_keyword'] = $newRowToAdd['campaign_keyword'];
        $oldRowToUpdate['campaign_medium'] = $newRowToAdd['campaign_medium'];
        $oldRowToUpdate['campaign_source'] = $newRowToAdd['campaign_source'];
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
            'campaign_name' => '',
            'campaign_content' => '',
            'campaign_keyword' => '',
            'campaign_medium' => '',
            'campaign_source' => '',
            'quantity'        => 0,
            'revenue'         => 0,
            'unique_purchase' => 0,
        );
    }
}
