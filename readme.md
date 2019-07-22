# Laravel Queue Monitoring

## Installation

1. Add library in Composer
```
"require": {
    ...,
    "showheroes/laravel-queue-monitoring": "dev-master"
}
...
{
    "type": "vcs",
    "no-api": true,
    "url":  "git@github.com:showheroes/laravel-queue-monitoring.git"
}
```
2. Configure Queue Length Job in job kernel
Put code below in `app/Console/Kernel.php` in `schedule()` function
```
$schedule->command('laravel-queue-monitoring:report-queue-length --queues=default,custom')->everyMinute();
```
3. If you’re on Laravel 5.5 or later the package will be auto-discovered. Otherwise you will need to manually configure it in your `config/app.php`.
```
"providers" => array(
    // ...
    ShowHeroes\LaravelQueueMonitoring\LaravelQueueMonitoringServiceProvider::class,
)
```


## Development
If you are developing this module you can connect this library locally.

1. Add psr-4 config to composer.json
```
"autoload": {
    ...,
    "psr-4": {
        ...,
        "ShowHeroes\\LaravelQueueMonitoring\\": "../laravel-queue-monitoring/src"
    },
}
```

2. Add provider to `config/app.php` into `providers` array:
```
"providers" => array(
    // ...
    ShowHeroes\LaravelQueueMonitoring\LaravelQueueMonitoringServiceProvider::class,
)
```