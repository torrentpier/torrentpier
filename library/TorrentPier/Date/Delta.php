<?php

/**
 * Class Date_Delta
 */
class Date_Delta
{
	/**
	 * @type array
	 */
	var $auto_granularity = [
		60        => 'seconds',   // set granularity to "seconds" if delta less then 1 minute
		10800     => 'minutes',   // 3 hours
		259200    => 'hours',     // 3 days
		31363200  => 'mday',      // 12 months
		311040000 => 'mon',       // 10 years
	];
	/**
	 * @type array
	 */
	var $intervals = [];
	/**
	 * @type string
	 */
	var $format = '';

	function __construct()
	{
		global $lang;

		$this->intervals = $lang['DELTA_TIME']['INTERVALS'];
		$this->format    = $lang['DELTA_TIME']['FORMAT'];
	}

	/**
	 * @param        $first
	 * @param        $last
	 * @param string $from
	 *
	 * @return bool|string
	 */
	public function spellDelta ($first, $last, $from = 'auto')
	{
		if ($last < $first)
		{
			$old_first = $first;
			$first     = $last;
			$last      = $old_first;
		}

		if ($from == 'auto')
		{
			$from = 'year';
			$diff = $last - $first;
			foreach ($this->auto_granularity as $seconds_count => $granule)
			{
				if ($diff < $seconds_count)
				{
					$from = $granule;
					break;
				}
			}
		}

		$delta = $this->getDelta($first, $last);
		if (!$delta)
		{
			return false;
		}

		$parts     = [];

		foreach (array_reverse($delta) as $k => $n)
		{
			if (!$n)
			{
				if ($k == $from)
				{
					if (!$parts)
					{
						$parts[] = declension($n, $this->intervals[$k], $this->format);
					}
					break;
				}
				continue;
			}
			$parts[] = declension($n, $this->intervals[$k], $this->format);
			if ($k == $from)
			{
				break;
			}
		}

		return join(' ', $parts);
	}

	/**
	 * @param $first
	 * @param $last
	 *
	 * @return bool
	 */
	public function getDelta ($first, $last)
	{
		if ($last < $first)
		{
			return false;
		}

		// Solve H:M:S part.
		$hms              = ($last - $first) % (3600 * 24);
		$delta['seconds'] = $hms % 60;
		$delta['minutes'] = floor($hms / 60) % 60;
		$delta['hours']   = floor($hms / 3600) % 60;

		// Now work only with date, delta time = 0.
		$last -= $hms;
		$f = getdate($first);
		$l = getdate($last); // the same daytime as $first!

		$dYear = $dMon = $dDay = 0;

		// Delta day. Is negative, month overlapping.
		$dDay += $l['mday'] - $f['mday'];
		if ($dDay < 0)
		{
			$monlen = $this->_monthLength(date('Y', $first), date('m', $first));
			$dDay += $monlen;
			$dMon--;
		}
		$delta['mday'] = $dDay;

		// Delta month. If negative, year overlapping.
		$dMon += $l['mon'] - $f['mon'];
		if ($dMon < 0)
		{
			$dMon += 12;
			$dYear--;
		}
		$delta['mon'] = $dMon;

		// Delta year.
		$dYear += $l['year'] - $f['year'];
		$delta['year'] = $dYear;

		return $delta;
	}

	/**
	 * @param $year
	 * @param $mon
	 *
	 * @return int
	 */
	private function _monthLength ($year, $mon)
	{
		$l = 28;
		while (checkdate($mon, $l + 1, $year))
		{
			$l++;
		}

		return $l;
	}
}