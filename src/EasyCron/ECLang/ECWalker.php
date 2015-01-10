<?php
namespace EasyCron\ECLang;

use EasyCron\ECLang\AST\DetermineAtStatement;
use EasyCron\ECLang\AST\DetermineEveryStatement;
use EasyCron\ECLang\AST\DetermineInStatement;
use EasyCron\ECLang\AST\DetermineOnStatement;
use EasyCron\ECLang\AST\DetermineOnTheStatement;
use EasyCron\ECLang\AST\ExecuteStatement;

class ECWalker {
    protected $_components = array(
        'minute'   => null,
        'hour'     => null,
        'weekday'  => null,
        'month'    => null,
        'monthday' => null,
        'year'     => null,
        'command'  => null
    );

    public function walk(array $astCollection)
    {
        foreach ($astCollection as $AST)
        {
            switch (true)
            {
                case $AST instanceof DetermineEveryStatement:
                    $this->walkDetermineEveryStatement($AST);
                    break;
                case $AST instanceof DetermineAtStatement:
                    $this->walkDetermineAtStatement($AST);
                    break;
                case $AST instanceof DetermineOnStatement:
                    $this->walkDetermineOnStatement($AST);
                    break;
                case $AST instanceof DetermineInStatement:
                    $this->walkDetermineInStatement($AST);
                    break;
                case $AST instanceof DetermineOnTheStatement:
                    $this->walkDetermineOnTheStatement($AST);
                    break;
                case $AST instanceof ExecuteStatement:
                    $this->walkExecuteStatement($AST);
                    break;
            }
        }

        $components = array_map(function($value) {
            return trim(is_null($value) ? '*' : $value);
        }, $this->_components);

        $cronString = $components['minute'] . ' ' .
                        $components['hour'] . ' ' .
                        $components['monthday'] . ' ' .
                        $components['month'] . ' ' .
                        $components['weekday'] . ' ' .
                        $components['command'];

        return $cronString;
    }

    public function getComponents()
    {
        return $this->_components;
    }

    public function walkExecuteStatement(ExecuteStatement $AST)
    {
        $this->_components['command'] = $AST->getCommand();
    }

    public function walkDetermineOnTheStatement(DetermineOnTheStatement $AST)
    {
        $this->_components['monthday'] = implode(',', $AST->getDays());
    }

    public function walkDetermineInStatement(DetermineInStatement $AST)
    {
        $valueMap = array(
            'jan(?:urary)?' => 1,
            'feb(?:urary)?' => 2,
            'mar(?:ch)?' => 3,
            'apr(?:il)?' => 4,
            'may' => 5,
            'jun(?:e)?' => 6,
            'jul(?:y)?' => 7,
            'aug(?:ust)?' => 8,
            'sep(?:tember)?' => 9,
            'oct(?:ober)?' => 10,
            'nov(?:ember)?' => 11,
            'dec(?:ember)?' => 12
        );

        $mappedMonths = $this->_mapValues($AST->getMonths(), $valueMap);
        asort($mappedMonths);
        $this->_components['month'] = implode(',', $mappedMonths);
    }

    public function walkDetermineAtStatement(DetermineAtStatement $AST)
    {
        $times = $AST->getTimes();
        $mappedTimes = array();
        foreach ($times as $time) {
            preg_match('/(?<int>\d+)(?<meridiem>\w+)/i', $time, $matches);
            $mappedTimes[] = $matches['int'] + ($matches['meridiem'] == 'pm' ? 12 : 0);
        }

        asort($mappedTimes);
        $this->_components['hour'] = implode(',', $mappedTimes);
    }

    public function walkDetermineOnStatement(DetermineOnStatement $AST)
    {
        $days = $AST->getDays();
        $valueMap = array(
            'sun' => 0,
            'mon' => 1,
            'tue(?:s)?' => 2,
            'wed(?:nes)?' => 3,
            'thu(?:s)?' => 5,
            'fri(?:day)?' => 6,
            'sat(?:ur)?' => 7
        );

        $mappedDays = $this->_mapValues($days, $valueMap);
        asort($mappedDays);
        $this->_components['weekday'] = implode(',', $mappedDays);
    }

    public function walkDetermineEveryStatement(DetermineEveryStatement $AST)
    {
        $amount = $AST->getAmount();
        $cronLine = '';
        if ($amount == 0) {
            $cronLine = '0';
        }elseif ($amount == 1) {
            $cronLine = '*';
        } elseif ($amount > 1) {
            $cronLine = '*/' . (int)$AST->getAmount();
        }

        $order = array('minute', 'hour', 'weekday', 'month');

        $frequency = rtrim($AST->getFrequency(), 's');
        if (array_key_exists($frequency, $this->_components)) {
            if (empty($this->_components[$frequency])) {
                $this->_components[$frequency] = $cronLine;
            }
        }
    }

    protected function _mapValues($values, $valueMap)
    {
        $mappedValues = array();
        foreach ($values as $day) {
            foreach ($valueMap as $key => $value) {
                $regex = '/' . $key . '(?:day)?/i';
                if (preg_match($regex, $day)) {
                    $mappedValues[] = $value;
                }
            }
        }

        return $mappedValues;
    }
}