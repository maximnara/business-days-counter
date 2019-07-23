# Business Days Counter
Returns difference between dates without public holidays and weekends. Works for different countries.

## Installation

Add library in Composer
```
composer require maximnara/business-days-counter
```


## Development
If you are developing this module you can connect this library locally.

1. Add psr-4 config to composer.json
```
"require": {
    ...,
    "maximnara/business-days-counter": "*"
},
"repositories": [
    ...,
    {
        "type": "path",
        "url": "../business-days-counter"
    }
]
```

2. Add provider to `config/app.php` into `providers` array:
```
"providers" => array(
    ...,
    ShowHeroes\LaravelQueueMonitoring\LaravelQueueMonitoringServiceProvider::class,
)
```

## Config
In your config/services.php set this
```
'business-days-counter' => [
    'country' => 'lv',
    'working-hours' => [
        'from' => 9,
        'to' => 18,
        'launch-hour' => 14, // This is launch hour start and it goes till 15
    ],
],
```

## How to use

```
use maximnara\BusinessDaysCounter\DatesCounter;

public function __construct(DatesCounter $datesCounter)
{
    $this->datesCounter = $datesCounter;
}

public function action()
{
    $diffInSeconds = $this->datesCounter->getDifferenceInSeconds($date1, $date2, DateCounter::COUNTRY_FR); // Here only Carbon dates.
}
```