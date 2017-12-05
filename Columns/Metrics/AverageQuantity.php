<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\RetailReport\Columns\Metrics;

use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * The average quantity for each order. Calculated as:
 *
 *     quantity / unique_purchase
 *
 */
class AverageQuantity extends ProcessedMetric
{
    private $idSite;

    public function getName()
    {
        return 'avg_quantity';
    }

    public function compute(Row $row)
    {
        $revenue = $this->getMetric($row, 'quantity');
        $conversions = $this->getMetric($row, 'unique_purchase');

        return Piwik::getQuotientSafe($revenue, $conversions, $precision = 2);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('RetailReport_AverageQuantity');
    }

    public function getDependentMetrics()
    {
        return array('quantity', 'unique_purchase');
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTableFactory::getSiteIdFromMetadata($table);
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }
}


