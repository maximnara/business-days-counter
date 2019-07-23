<?php

namespace maximnara\BusinessDaysCounter;

use Illuminate\Support\ServiceProvider;

class BusinessDaysCounterServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(
			DatesCounter::class,
			function ($app) {
				$country = config('services.business-days-counter.country');
				$workingHoursFrom = config('services.business-days-counter.working-hours.from');
				$workingHoursTo = config('services.business-days-counter.working-hours.to');
				$launchHour = config('services.business-days-counter.working-hours.launch-hour');
				return new DatesCounter($country, $workingHoursFrom, $workingHoursTo, $launchHour);
			}
		);
	}
}