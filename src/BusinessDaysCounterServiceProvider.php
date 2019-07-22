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
				return new DatesCounter();
			}
		);
	}
}