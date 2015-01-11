<?php
namespace EasyCron\Cron;

use \RuntimeException;

class Creator implements CreatorInterface
{
    protected $_components = array(
        'minute'   => null,
        'hour'     => null,
        'day'      => null,
        'month'    => null,
        'week'     => null,
        'command'  => null
    );


    /**
     * Compile given values into a cron string
     *
     * @return string
     */
    public function getLine()
    {
        $components = array_map(function($val) {
            return is_null($val) ? '*' : $val;
        }, $this->_components);

        return implode(' ', $components);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLine();
    }

    /**
     * Executing CronJob in regular arbitrary intervals
     *
     * @param int $amount
     * @param string $unit
     */
    public function every($amount, $unit)
    {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('$amount MUST be numeric');
        }

        $amount = (int)$amount;
        if ($amount < 1) {
            throw new RuntimeException('$amount MUST be greater than 0');
        }

        $validUnits = array('week', 'month', 'day', 'hour', 'minute');
        if (!in_array($unit, $validUnits)) {
            throw new \InvalidArgumentException('Invalid unit ' . $unit);
        }

        // There's no syntax for executing cronjobs every X weeks or X months so
        // We insert a bit of bash that test for this condition
       if ($unit == 'week' && $amount > 1) {
           $command = $this->_components['command'];
           $this->_components['command'] = 'test $(($(date +\%W)\%'.$amount.')) -eq 1 && ' . $command;
           $this->_components['minute'] = 0;
           $this->_components['hour'] = 0;

           return;
       }elseif ($unit == 'month' && $amount > 1) {
           $command = $this->_components['command'];
           $this->_components['command'] = 'test $(($(date +\%m)\%'.$amount.')) -eq 1 && ' . $command;
           $this->_components['minute'] = 0;
           $this->_components['day'] = 0;
           $this->_components['hour'] = 0;

           return;
       }

        // 1 is always translated to * since */1 is redundant
        if ($amount == 1) {
            $this->_components[$unit] = '*';
        } else {
            $this->_components[$unit] = '*/' . $amount;
        }


        // Grab the units lower in the cron line than the one we're effecting now and set them to 0 so we execute
        // right as the desired every condition takes place
        $weightedUnits = array('minute', 'hour', 'day', 'month', 'week');
        $unitPos = array_search($unit, $weightedUnits);
        $lesserUnits = array_slice($weightedUnits, 0, $unitPos);
        foreach ($lesserUnits as $lesserUnit) {
            if ($unit == 'week' && in_array($lesserUnit, array('day', 'month'))) {
                continue;
            }

            $this->_components[$lesserUnit] = 0;
        }
    }

    /**
     * Specifying the weekdays (monday-friday) that the cronjob will be executed.
     * Handles string mon-fri or 0-6
     *
     * @param array $weekdays
     */
    public function on(array $weekdays)
    {
        $validWeekdays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $convertedWeekdays = $this->_convert(array(0, 6), $validWeekdays, $weekdays);
        $lesserUnits = array('minute', 'hour');
        foreach ($lesserUnits as $lesserUnit) {
            if (is_null($this->_components[$lesserUnit]) && !strstr($this->_components['minute'], '*')) {
                $this->_components[$lesserUnit] = 0;
            }
        }

        $this->_components['week'] = implode(',', $convertedWeekdays);

    }

    /**
     * Specify the days of the month the cronjob will be executed.
     *
     * @param array $days
     */
    public function onThe(array $days)
    {
        foreach ($days as $k => $day) {
            if (!is_numeric($day)) {
                throw new RuntimeException('$day MUST be numeric');
            }

            $days[$k] = (int)$day;
        }

        $lesserUnits = array('hour', 'minute');
        foreach ($lesserUnits as $lesserUnit) {
            if (is_null($this->_components[$lesserUnit])) {
                if ($lesserUnit == 'hour' && strstr($this->_components['minute'], '*')) {
                    $this->_components[$lesserUnit] = '*';
                } else {
                    $this->_components[$lesserUnit] = 0;
                }
            }
        }

        $this->_components['day'] = implode(',', $days);
    }

    /**
     * Specify times of day that cronjob can be executed. Handles 00-24.
     *
     * @param array $times
     */
    public function at(array $times)
    {
        foreach ($times as $k => $time) {
            if (!is_numeric($time)) {
                throw new RuntimeException('$time must be an int');
            }elseif ($time > 24 || $time < 0) {
                throw new RuntimeException('$time must be between 0 and 24');
            }

            $times[$k] = (int)$time;
        }

        if (!strstr($this->_components['minute'], '*')) {
            $this->_components['minute'] = 0;
        }

        $this->_components['hour'] = implode(',', $times);
    }

    /**
     * Specifying the months a cronjob is executed in. Handles jan-dec or 1-12
     *
     * @param array $months
     */
    public function in(array $months)
    {
        $validMonths = array(
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december',
        );

        $convertedMonths = $this->_convert(array(1, 31), $validMonths, array_map('strtolower', $months));
        $lesserUnits = array('minute', 'hour', 'day');
        foreach ($lesserUnits as $lesserUnit) {
            if (is_null($this->_components[$lesserUnit]) && !strstr($this->_components['minute'], '*')) {
                if (in_array($lesserUnit, array('hour', 'minute'))) {
                    $this->_components[$lesserUnit] = 0;
                } else {
                    $this->_components[$lesserUnit] = 1;
                }
            }
        }

        $this->_components['month'] = implode(',', array_map(function($value) {
            return ++$value;
        }, $convertedMonths));
    }

    /**
     * Specify the command to be executed.
     *
     * @param string $command
     */
    public function execute($command)
    {
        $this->_components['command'] .= $command;
    }

    /**
     * Convert an array of values to all ints, may be mixed. The keys
     * of $validValues will be the value returned if $value is a string
     *
     * @param array $acceptableRange
     * @param array $validValues
     * @param array $values
     * @return array
     */
    protected function _convert(array $acceptableRange, array $validValues, array $values)
    {

        if (count($acceptableRange) != 2 || !array_key_exists(0, $acceptableRange) || !array_key_exists(1, $acceptableRange)) {
            throw new RuntimeException('$acceptableRange must be a 2 element array containing 0 => low and 1 => high');
        }

        if (!is_int($acceptableRange[0]) || !is_int($acceptableRange[1])) {
            throw new RuntimeException('values of $acceptableRange must be ints');
        }

        $convertedValues = array();
        foreach ($values as $value) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new RuntimeException('Members of $values must be numeric or a string');
            }

            if (is_numeric($value)) {
                if ($value < $acceptableRange[0] || $value > $acceptableRange[1]) {
                    throw new RuntimeException('Supplied value must be between '. $acceptableRange[0] .' and ' . $acceptableRange[1]);
                }

                $convertedValues[] = $value;
            } else {
                if (!in_array($value, $validValues)) {
                    throw new RuntimeException($value . ' is not within range of valid values: ' . implode(',', $validValues));
                }

                $convertedValues[] = array_search($value, $validValues);
            }
        }

        return $convertedValues;
    }
}