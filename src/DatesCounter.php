<?php

namespace maximnara\BusinessDaysCounter;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class DatesCounter
{
	const COUNTRY_LV = 'lv';
	const COUNTRY_DE = 'de';
	const COUNTRY_FR = 'fr';
	const COUNTRY_RU = 'ru';
	const COUNTRY_CZ = 'cz';

	public $country;
	public $workingHoursFrom;
	public $workingHoursTo;
	public $launchHour;

	public function __construct($country = null, $workingHoursFrom = null, $workingHoursTo = null, $launchHour = null)
	{
		$this->country = $country;
		$this->workingHoursFrom = $workingHoursFrom;
		$this->workingHoursTo = $workingHoursTo;
		$this->launchHour = $launchHour;
	}

	public function getDifferenceInSeconds(Carbon $dateFrom, Carbon $dateTo, $country = null)
	{
		if ($dateFrom > $dateTo) {
			$date = $dateFrom;
			$dateFrom = $dateTo;
			$dateTo = $date;
		}
		$country = $country ?? $this->country;
		$workingHoursIsEnabled = !is_null($this->workingHoursFrom) && !is_null($this->workingHoursTo);
		$launchHourEnabled = !is_null($this->launchHour);
		if (empty($country)) {
			throw new \Exception('BusinessDaysCounter: You should provide country code.');
		}
		$currentDate = $dateFrom->copy();
		$diffInSeconds = 0;
		while ($currentDate->lessThanOrEqualTo($dateTo)) {
			$nextDayStart = $currentDate->copy()->addDay()->startOfDay();

			if ($workingHoursIsEnabled && $launchHourEnabled) {
				$startOfWorkingDay = $currentDate->copy()->hour($this->workingHoursFrom)->minute(0)->second(0);
				$endOfWorkingDay = $currentDate->copy()->hour($this->workingHoursTo)->minute(0)->second(0);
				$startOfLaunch = $currentDate->copy()->hour($this->launchHour)->minute(0)->second(0);
				$endOfLaunch = $startOfLaunch->copy()->addHour();

				if ($this->isWeekend($currentDate, $country)) { // Holiday, drop the date.
					$currentDate->addDay()->startOfDay();
					continue;
				}
				/** Count from start of working day till end of working day. */
				else if ($currentDate->lessThanOrEqualTo($startOfWorkingDay) && $dateTo->greaterThan($endOfWorkingDay)) {
					$diffInSeconds += $startOfWorkingDay->diffInSeconds($startOfLaunch);
					$diffInSeconds += $endOfLaunch->diffInSeconds($endOfWorkingDay);
				}
				/** When we start work after work day start but before launch. Count From start of real work till end of working day. */
				else if ($currentDate->greaterThan($startOfWorkingDay) && $currentDate->lessThan($startOfLaunch) && $endOfWorkingDay->lessThanOrEqualTo($dateTo)) {
					$diffInSeconds += $currentDate->diffInSeconds($startOfLaunch);
					$diffInSeconds += $endOfLaunch->diffInSeconds($endOfWorkingDay);
				}
				/** We started work on launch and count until official working day. */
				else if ($currentDate->greaterThan($startOfWorkingDay) && $currentDate->greaterThanOrEqualTo($startOfLaunch) && $currentDate->lessThanOrEqualTo($endOfLaunch) && $endOfWorkingDay->lessThanOrEqualTo($dateTo)) { //
					$diffInSeconds += $endOfLaunch->diffInSeconds($endOfWorkingDay);
				}
				/** Start from official start and end after launch but before official end. */
				else if ($currentDate->lessThanOrEqualTo($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($endOfLaunch)) {
					$diffInSeconds += $startOfWorkingDay->diffInSeconds($startOfLaunch);
					$diffInSeconds += $endOfLaunch->diffInSeconds($dateTo);
				}
				/** Start from official day start and end on launch. */
				else if ($currentDate->lessThanOrEqualTo($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($startOfLaunch) && $dateTo->lessThanOrEqualTo($endOfLaunch)) {
					$diffInSeconds += $startOfWorkingDay->diffInSeconds($startOfLaunch);
				}
				/** Start after official day beginning and end after launch */
				else if ($currentDate->greaterThan($startOfWorkingDay) && $currentDate->lessThanOrEqualTo($startOfLaunch) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($endOfLaunch)) {
					$diffInSeconds += $currentDate->diffInSeconds($startOfLaunch);
					$diffInSeconds += $endOfLaunch->diffInSeconds($dateTo);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($startOfWorkingDay) && $dateTo->greaterThan($startOfLaunch) && $dateTo->lessThanOrEqualTo($endOfLaunch)) {
					$diffInSeconds += $currentDate->diffInSeconds($startOfLaunch);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($startOfLaunch)) {
					$diffInSeconds += $currentDate->diffInSeconds($dateTo);
				}
				/** We start to work when it was launch. And we end day before it officially ends. Counting from end of launch till real end of day. */
				else if ($currentDate->greaterThan($startOfWorkingDay) && $currentDate->greaterThan($startOfLaunch) && $currentDate->lessThanOrEqualTo($endOfLaunch) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($endOfLaunch)) {
					$diffInSeconds += $endOfLaunch->diffInSeconds($dateTo);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $currentDate->greaterThan($endOfLaunch) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($endOfLaunch)) {
					$diffInSeconds += $currentDate->diffInSeconds($dateTo);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $currentDate->greaterThan($endOfLaunch) && $dateTo->greaterThan($endOfWorkingDay)) {
					$diffInSeconds += $currentDate->diffInSeconds($endOfWorkingDay);
				} else if ($currentDate->lessThanOrEqualTo($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($startOfLaunch)) {
					$diffInSeconds += $startOfWorkingDay->diffInSeconds($dateTo);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($startOfLaunch)) {
					$diffInSeconds += $currentDate->diffInSeconds($dateTo);
				}
				$currentDate = $nextDayStart;
			} else if ($workingHoursIsEnabled) {
				$startOfWorkingDay = $currentDate->copy()->hour($this->workingHoursFrom)->minute(0)->second(0);
				$endOfWorkingDay = $currentDate->copy()->hour($this->workingHoursTo)->minute(0)->second(0);

				if ($this->isWeekend($currentDate, $country)) {
					$currentDate->addDay()->startOfDay();
					continue;
				} else if ($currentDate->lessThanOrEqualTo($startOfWorkingDay) && $dateTo->greaterThan($endOfWorkingDay)) {
					$diffInSeconds += $startOfWorkingDay->diffInSeconds($endOfWorkingDay);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $endOfWorkingDay->lessThanOrEqualTo($dateTo)) {
					$diffInSeconds += $currentDate->diffInSeconds($endOfWorkingDay);
				} else if ($currentDate->lessThanOrEqualTo($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($startOfWorkingDay)) {
					$diffInSeconds += $startOfWorkingDay->diffInSeconds($dateTo);
				} else if ($currentDate->greaterThan($startOfWorkingDay) && $dateTo->lessThanOrEqualTo($endOfWorkingDay) && $dateTo->greaterThan($startOfWorkingDay)) {
					$diffInSeconds += $currentDate->diffInSeconds($dateTo);
				}
				$currentDate = $nextDayStart;
			} else {
				if ($this->isWeekend($currentDate, $country)) {
					$currentDate->addDay()->startOfDay();
					continue;
				} else if ($nextDayStart->lessThanOrEqualTo($dateTo)) {
					$diffInSeconds += $currentDate->diffInSeconds($nextDayStart);
				} else {
					$diffInSeconds += $currentDate->diffInSeconds($dateTo);
				}
				$currentDate = $nextDayStart;
			}
		}
		return $diffInSeconds;
	}

	private function isWeekend(Carbon $date, $country)
	{
		$holidays = $this->getCountryHolidaysData($country, $date->year);
		$isPublicHoliday = false;
		foreach ($holidays as $holiday) {
			if ($date->format('Y-m-d') == $holiday->format('Y-m-d')) {
				$isPublicHoliday = true;
				break;
			}
		}
		return $isPublicHoliday || $date->isWeekend();
	}

	private function getCountryHolidaysData($country, $year)
	{
		$rawData = $this->getCountryHolidaysRawData($country, $year);
		$holidays = $rawData['holidays'];
		$publicHolidays = [];
		foreach ($holidays as $holiday) {
			if (in_array('National holiday', $holiday['type'])) {
				$publicHolidays[] = Carbon::createFromFormat('Y-m-d', $holiday['date']['iso']);
			}
		}
		return $publicHolidays;
	}

	private function getCountryHolidaysRawData($country, $year)
	{
		$path = __DIR__ . "/../resources/$year/$country.json";
		return json_decode(file_get_contents($path), true);
	}
}