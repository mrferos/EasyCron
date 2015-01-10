<?php
namespace EasyCron;

use \RuntimeException;

class CronCreator
{
    protected $_components;

    public function every($amount, $unit)
    {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('$amount MUST be numeric');
        }

        $validUnits = array('week', 'month', 'day', 'hour', 'minute');
        if (!array_key_exists($unit, $validUnits)) {
            throw new \InvalidArgumentException('Invalid unit ' . $unit);
        }

        $amount = (int)$amount;
    }

    public function on(array $weekdays)
    {
        $validWeekdays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $convertedWeekdays = $this->_convert(array(0, 6), $validWeekdays, $weekdays);
    }

    public function onThe(array $days)
    {

    }

    public function at(array $times)
    {

    }

    public function in(array $months)
    {
        $validMonths = array(
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july ',
            'august',
            'september',
            'october',
            'november',
            'december',
        );

        $convertedMonths = $this->_convert(array(1, 31), $validMonths, $months);
    }

    public function execute($command)
    {

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