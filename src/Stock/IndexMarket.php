<?php
/**
 * Moscow Exchange ISS Client
 *
 * @link      http://github.com/panychek/moex
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @author    Igor Panychek panychek@gmail.com
 */

namespace Panychek\MoEx\Stock;

use Panychek\MoEx\Market;
use Panychek\MoEx\Client;
use Panychek\MoEx\Exception\InvalidArgumentException;

class IndexMarket extends Market
{
    /**
     * @var string
     */
    protected $id = 'index';
    
    /**
     * @var array
     */
    protected $market_data_mappings = array(
        'value' => 'currentvalue',
        'openingvalue' => 'openvalue',
        'previousclose' => 'lastvalue',
        'dailylow' => 'low',
        'dailyhigh' => 'high'
    );
    
    /**
     * @return callable
     */
    public function getVolumeGetter()
    {
        /**
         * Get the volume
         *
         * @param  array $market_data
         * @param  array $arguments
         * @return mixed
         */
        return function(array $market_data, array $arguments) {
            $currency = (!empty($arguments[0])) ? $arguments[0] : 'rub';
            $currency = strtolower($currency);
            $this->validateCurrency($currency);
            
            $field = 'valtoday';
            if ($currency == 'usd') {
                $field .= '_usd';
            }
            
            return $market_data[$field];
        };
    }
    
    /**
     * @return callable
     */
    public function getChangeGetter()
    {
        /**
         * Get the change
         *
         * @param  array $market_data
         * @param  array $arguments
         * @throws \Panychek\MoEx\Exception\InvalidArgumentException
         * @return mixed
         */
        return function(array $market_data, array $arguments) {
            $range = (!empty($arguments[0])) ? $arguments[0] : 'day';
            $measurement = (!empty($arguments[1])) ? $arguments[1] : 'points';
            
            $range = strtolower($range);
            $range_mappings = array(
                'mtd' => 'month',
                'ytd' => 'year',
                'day' => 'last'
            );
            
            if (empty($range_mappings[$range])) {
                $message = 'Unsupported range. Available ranges: "MTD", "YTD" and "day"';
                throw new InvalidArgumentException($message);
            }
            
            $suffix = ($measurement == '%') ? 'prc' : 'bp';
            
            $field = sprintf('%schange%s', $range_mappings[$range], $suffix);
            return $market_data[$field];
        };
    }
    
    /**
     * @return callable
     */
    public function getCapitalizationGetter()
    {
        /**
         * Get the capitalization
         *
         * @param  array $market_data
         * @param  array $arguments
         * @return int
         */
        return function(array $market_data, array $arguments) {
            $currency = (!empty($arguments[0])) ? $arguments[0] : 'rub';
            $currency = strtolower($currency);
            $this->validateCurrency($currency);
            
            $field = 'capitalization';
            if ($currency == 'usd') {
                $field .= '_usd';
            }
            
            return $market_data[$field];
        };
    }
    
    /**
     * @return callable
     */
    public function getLastUpdateGetter()
    {
        /**
         * Get the date of the last update
         * 
         * @param  array $market_data
         * @return \DateTime
         */
        return function(array $market_data) {
            $date = $market_data['tradedate'];
            $time = $market_data['updatetime'];
            $datetime_str = sprintf('%s %s', $date, $time);
            
            $timezone = new \DateTimeZone(Client::TIMEZONE);
            return new \DateTime($datetime_str, $timezone);
        };
    }
}
