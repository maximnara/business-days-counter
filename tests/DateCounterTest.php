<?php

namespace maximnara\BusinessDaysCounter\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use maximnara\BusinessDaysCounter\DatesCounter;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestDateCounter extends OrchestraTestCase
{
	public function testWorkingHoursWithLaunchHour()
	{
		/** @var DatesCounter $datesCounter */
		$datesCounter = app(DatesCounter::class);
		$this->config($datesCounter, DatesCounter::COUNTRY_LV, 9, 18, 14);

		$workingDay = 8 * 60 * 60;
		$theeFullWorkingDays = 3 * $workingDay;

		// Don't count Christmas day
		$dateFrom = Carbon::create(2019, 12, 26, 8, 30, 0);
		$dateTo = Carbon::create(2019, 12, 27, 18, 0, 0);
		$this->assertEquals($workingDay, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Full working days
		$dateFrom = Carbon::create(2019, 7, 17, 9, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working days starts 1 hour later
		$dateFrom = Carbon::create(2019, 7, 17, 10, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays - (60 * 60 * 1), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working time end 1 hour earlier
		$dateFrom = Carbon::create(2019, 7, 17, 9, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 17, 0, 0);
		$this->assertEquals($theeFullWorkingDays - (60 * 60 * 1), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working time starts on launch, so should starts after launch
		$dateFrom = Carbon::create(2019, 7, 17, 14, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays - (60 * 60 * 5), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working time starts on launch and ends 1 hour earlier
		$dateFrom = Carbon::create(2019, 7, 17, 14, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 17, 0, 0);
		$this->assertEquals($theeFullWorkingDays - (60 * 60 * 6), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts before official working day
		$dateFrom = Carbon::create(2019, 7, 17, 8, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working day ends after official working day
		$dateFrom = Carbon::create(2019, 7, 17, 8, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 19, 0, 0);
		$this->assertEquals($theeFullWorkingDays, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts and ends in one day
		$dateFrom = Carbon::create(2019, 7, 17, 8, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 17, 0, 0);
		$this->assertEquals(7 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working peiod starts and ends on launch
		$dateFrom = Carbon::create(2019, 7, 17, 14, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 14, 30, 0);
		$this->assertEquals(0, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period ends right before launch
		$dateFrom = Carbon::create(2019, 7, 17, 9, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 14, 0, 0);
		$this->assertEquals(5 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts 1 hour later and ends right before launch
		$dateFrom = Carbon::create(2019, 7, 17, 10, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 14, 0, 0);
		$this->assertEquals(4 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts 1 hour later and ends on launch
		$dateFrom = Carbon::create(2019, 7, 17, 10, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 14, 34, 0);
		$this->assertEquals(4 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts on launch and ends 1 hour earlier
		$dateFrom = Carbon::create(2019, 7, 17, 14, 30, 0);
		$dateTo = Carbon::create(2019, 7, 17, 17, 0, 0);
		$this->assertEquals(2 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts on launch and ends next day at the end
		$dateFrom = Carbon::create(2019, 7, 17, 14, 30, 0);
		$dateTo = Carbon::create(2019, 7, 18, 18, 0, 0);
		$this->assertEquals((3 * 60 * 60) + (8 * 60 * 60), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts on launch and next day 1 hour earlier
		$dateFrom = Carbon::create(2019, 7, 17, 14, 30, 0);
		$dateTo = Carbon::create(2019, 7, 18, 17, 0, 0);
		$this->assertEquals((2 * 60 * 60) + (8 * 60 * 60), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));
	}

	public function testWorkingDayWithoutLaunch()
	{
		/** @var DatesCounter $datesCounter */
		$datesCounter = app(DatesCounter::class);
		$this->config($datesCounter, DatesCounter::COUNTRY_LV, 9, 18);

		$workingDay = 9 * 60 * 60;
		$theeFullWorkingDays = 3 * $workingDay;

		// Don't count Christmas day
		$dateFrom = Carbon::create(2019, 12, 26, 8, 30, 0);
		$dateTo = Carbon::create(2019, 12, 27, 18, 0, 0);
		$this->assertEquals($workingDay, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Full working days
		$dateFrom = Carbon::create(2019, 7, 17, 9, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working days starts 1 hour later
		$dateFrom = Carbon::create(2019, 7, 17, 10, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays - (60 * 60 * 1), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working time end 1 hour earlier
		$dateFrom = Carbon::create(2019, 7, 17, 9, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 17, 0, 0);
		$this->assertEquals($theeFullWorkingDays - (60 * 60 * 1), $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts before official working day
		$dateFrom = Carbon::create(2019, 7, 17, 8, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 18, 0, 0);
		$this->assertEquals($theeFullWorkingDays, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working day ends after official working day
		$dateFrom = Carbon::create(2019, 7, 17, 8, 0, 0);
		$dateTo = Carbon::create(2019, 7, 19, 19, 0, 0);
		$this->assertEquals($theeFullWorkingDays, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts and ends in one day
		$dateFrom = Carbon::create(2019, 7, 17, 8, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 17, 0, 0);
		$this->assertEquals(8 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));

		// Working period starts 1 hour later and ends at the same day
		$dateFrom = Carbon::create(2019, 7, 17, 10, 0, 0);
		$dateTo = Carbon::create(2019, 7, 17, 14, 0, 0);
		$this->assertEquals(4 * 60 * 60, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo));
	}

	public function testDifferentCountries()
	{
		/** @var DatesCounter $datesCounter */
		$datesCounter = app(DatesCounter::class);
		$this->config($datesCounter, null, 9, 18);
		$workingDay = 9 * 60 * 60;

		// Don't count Christmas day in Germany
		$dateFrom = Carbon::create(2019, 12, 26, 8, 30, 0);
		$dateTo = Carbon::create(2019, 12, 27, 18, 0, 0);
		$this->assertEquals($workingDay, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo, DatesCounter::COUNTRY_DE));

		// Don't count Christmas day in Germany
		$dateFrom = Carbon::create(2019, 1, 1, 8, 30, 0);
		$dateTo = Carbon::create(2019, 1, 2, 18, 0, 0);
		$this->assertEquals($workingDay, $datesCounter->getDifferenceInSeconds($dateFrom, $dateTo, DatesCounter::COUNTRY_FR));
	}

	private function config(DatesCounter $datesCounter, $country, $workingFrom = null, $workingTo = null, $launchHour = null)
	{
		$datesCounter->country = $country;
		$datesCounter->workingHoursFrom = $workingFrom;
		$datesCounter->workingHoursTo = $workingTo;
		$datesCounter->launchHour = $launchHour;
	}
}
