Moscow Exchange ISS Client
=======================

[![Build Status](https://travis-ci.org/panychek/moex.svg?branch=master)](https://travis-ci.org/panychek/moex)
[![codecov](https://codecov.io/gh/panychek/moex/branch/master/graph/badge.svg)](https://codecov.io/gh/panychek/moex)
[![Coverage Status](https://coveralls.io/repos/github/panychek/moex/badge.svg?branch=master)](https://coveralls.io/github/panychek/moex?branch=master)

A PHP library that provides easy access to [Moscow Exchange](https://www.moex.com/) data via its [Informational & Statistical Server](https://www.moex.com/a2920).

## Installation

Download via [Composer](https://getcomposer.org):

```sh
composer require panychek/moex
```

Include the autoloader:

```php
require_once 'vendor/autoload.php';
```

## Getting started

The basic usage pattern is to instantiate a `Security` object for the security you want to interact with. You can pass any string you want (in Russian or English) - the library will perform a search and pick the best match.

```php
use Panychek\MoEx\Security;
use Panychek\MoEx\Exception\ExceptionInterface as MoexException;

try {
    $security = new Security('Sberbank');
    $last_price = $security->getLastPrice();
    
} catch (MoexException $e) {
    echo $e->getMessage();
}
```

You can also access the security by its code (prefix it with a hash symbol (**#**)). This will be slightly faster, since you avoid an extra API call to find it.

```php
$security = new Security('#SBER');
```

The default language is **Russian**. You can switch to English like this:

```php
Exchange::setLanguage('en');
```

## Authentication
If you're subscribed to any paid services and want to access their private data, you should authenticate yourself first using your Passport account credentials:
```php
Exchange::authenticate($username, $password);
```

## Search ###
You can easily find the needed financial instrument. This call will return a collection of matched securities:
```php
$securities = Exchange::findSecurities('Alrosa');
```

You can also limit the results:
```php
$securities = Exchange::findSecurities('Gazprom', 5);
```

## Retrieving market data

### Shares, currency pairs and futures

Available methods:

| Method | Input parameters | Returns |
|---|---|---|
| getLastPrice | | float |
| getOpeningPrice | | float |
| getClosingPrice | | float |
| getDailyHigh | | float |
| getDailyLow | | float |
| getLastUpdate | | object(\DateTime) |
| getDailyChange | | float |
| getDailyPercentageChange | | float |
| getVolume | string **$currency** <br>*Possible values:* **RUB** or **USD**<br>*Defaults to:* **RUB** | int |

Fetching the ruble's exchange rate from the FX market:
> If omitted, the base currency defaults to US dollar
```php
$usd_rub = Exchange::getRubleRate();
$eur_rub = Exchange::getRubleRate('EUR');
```

### Bonds

In addition, bonds also support the following methods:

| Method | Input parameters | Returns |
|---|---|---|
| getYield | | float |
| getDuration | | int |
| getFaceValue | | string |
| getCouponValue | | string |
| getCouponRate | | string |
| getCouponDate | | string |
| getMaturityDate | | string |

### Indices

Available methods:

| Method | Input parameters | Returns |
|---|---|---|
| getValue | | float |
| getOpeningValue | | float |
| getPreviousClose | | float |
| getDailyHigh | | float |
| getDailyLow | | float |
| getLastUpdate | | object(\DateTime) |
| getChange | string **$range**<br>*Possible values:* **day**, **MTD** or **YTD**<br>*Defaults to:* **day**<br><br>string **$measurement**<br>*Possible values:* **points** or **%**<br>*Defaults to:* **points** | float or int |
| getVolume | string **$currency** <br>*Possible values:* **RUB** or **USD**<br>*Defaults to:* **RUB** | int |
| getCapitalization | string **$currency** <br>*Possible values:* **RUB** or **USD**<br>*Defaults to:* **RUB** | int |

###### Example usage
```php
$rtsi = new Security('RTS Index');

$year_to_date_return = $rtsi->getChange('YTD', '%');
$month_to_date_return = $rtsi->getChange('MTD', '%');

$volume_rub = $rtsi->getVolume('RUB');
$volume_usd = $rtsi->getVolume('USD');

$capitalization_rub = $rtsi->getCapitalization('RUB');
$capitalization_usd = $rtsi->getCapitalization('USD');
```

## Historical quotes

For a specific date range:

```php
$security = new Security('MICEX Index');
$data = $security->getHistoricalQuotes('2014-01-01', '2014-12-31');
```

Starting from a particular day:

```php
$data = $security->getHistoricalQuotes('2017-01-01');
```

## Profile

```php
$security = new Security('#GAZP');
$security->setLanguage('en');

$code = $security->getId(); // "GAZP"
$name = $security->getName(); // "Gazprom"
$short_name = $security->getShortName(); // "Gazprom"
$isin_code = $security->getIsin(); // "RU0007661625"

$market_title = $security->getMarket()->getTitle(); // "Equities Market"
$engine_title = $security->getEngine()->getTitle(); // "Securities Market"
$board_title = $security->getBoard()->getTitle(); // "T+: Stocks, DRs"
$capitalization = $security->getEngine()->getCapitalization(); // 33015281259414 RUB

$issuer_title = $security->getIssuer()->getTitle(); // "Публичное акционерное общество "Газпром""

$indices = $security->getIndices();
foreach ($indices as $index) {
    $code = $index->getId(); // "MICEX10INDEX", "MICEXBMI", "MICEXINDEXCF", etc.
    $short_name = $index->getShortName(); // "MICEX10 Index", "Moscow Exchange Broad Market Index", "MICEX Index", etc.
}
```

## Turnovers

### Totals for the exchange

Last available trade day:

```php
$exchange = $security->getExchange();

$turnovers_rub = $exchange->getTurnovers('rub'); 
$turnovers_usd = $exchange->getTurnovers('usd');
$num_trades = $exchange->getNumberOfTrades(); 
```

For a specific day:

```php
$christmas_turnovers = $exchange->getTurnovers('usd', '2015-12-25');
$christmas_num_trades = $exchange->getNumberOfTrades('2015-12-25');
```

### Totals for a specific market

Last available trade day:

```php
$security = new Security('USDRUB');
$fx_market = $security->getEngine();

$fx_turnovers_rub = $fx_market->getTurnovers('rub');
$fx_turnovers_usd = $security->getEngine()->getTurnovers('usd');
$fx_num_trades = $security->getEngine()->getNumberOfTrades(); 
```

For a specific day:

```php
$fx_christmas_turnovers = $fx_market->getTurnovers('usd', '2015-12-25');
$fx_christmas_num_trades = $fx_market->getNumberOfTrades('2015-12-25');
```

## Groups

Get the most reliable stocks (that are on the First Level quotation list):

```php
$shares_group = SecurityGroup::getInstance('stock_shares');
$first_level = $shares_group->getCollection('one');
$securities = $first_level->getSecurities();
```

## Debugging
To see what's going on at the network level, define a logging callback function and pass it to the client. Once done, it will be invoked on each API call with its statistics.
```php
$logger = function(\GuzzleHttp\TransferStats $stats) {
    $request = $stats->getRequest();    
    $uri = $request->getUri();
    
    $time = $stats->getTransferTime();
    
    $html = 'Request URI: %s<br />Time: %s<br /><br />';
    echo sprintf($html, $uri, $time);
};

Client::getInstance()->setRequestLogger($logger);
```

## Handling errors
A `Panychek\MoEx\Exception\DataException` exception is thrown in the event of any data related error.
The following codes indicate the reason for the failure:

 * `FAILED_REQUEST` - all network errors
 * `INVALID_RESPONSE` - the response is not a valid JSON string or its format is not supported
 * `EMPTY_RESULT` - the response contains no actual data

A `Panychek\MoEx\Exception\AuthenticationException` exception is thrown in case of authentication failure.

All exceptions thrown by the library implement the `Panychek\MoEx\Exception\ExceptionInterface` interface.

## Tests
There are two types of tests distributed with the package: unit tests and integration tests.

The unit tests are run against mock data and hence are ready out-of-the-box. In order to run them, execute the following command:

```sh
phpunit --group Unit
```

The integration tests make real API calls and ensure that nothing has changed with the responses, so they require network access. You can perform integration testing by running:

```sh
phpunit --group Integration
```
