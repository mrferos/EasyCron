<?php
namespace EasyCron\ECLang;

use EasyCron\Cron\Creator;
use EasyCron\Cron\CreatorInterface;
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

    /**
     * @var WriterInterface
     */
    protected $_cronWriter;
    /**
     * @var CreatorInterface
     */
    protected $_cronCreator;

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

        return $this->getCronCreator()->getLine();
    }

    /**
     * @return CreatorInterface
     */
    public function getCronCreator()
    {
        if (empty($this->_cronCreator)) {
            $this->setCronCreator(new Creator());
        }

        return $this->_cronCreator;
    }

    /**
     * @param CreatorInterface $cronCreator
     */
    public function setCronCreator(CreatorInterface $cronCreator)
    {
        $this->_cronCreator = $cronCreator;
    }

    public function walkExecuteStatement(ExecuteStatement $AST)
    {
        $command = $AST->getCommand();
        $this->getCronCreator()->execute(substr($command, 1, strlen($command) - 2));
    }

    public function walkDetermineOnTheStatement(DetermineOnTheStatement $AST)
    {
        $this->getCronCreator()->onThe($AST->getDays());
    }

    public function walkDetermineInStatement(DetermineInStatement $AST)
    {
        $valueMap = array(
            'jan(?:urary)?' => 0,
            'feb(?:urary)?' => 1,
            'mar(?:ch)?' => 2,
            'apr(?:il)?' => 3,
            'may' => 4,
            'jun(?:e)?' => 5,
            'jul(?:y)?' => 6,
            'aug(?:ust)?' => 7,
            'sep(?:tember)?' => 8,
            'oct(?:ober)?' => 9,
            'nov(?:ember)?' => 10,
            'dec(?:ember)?' => 11
        );

        $mappedMonths = $this->_mapValues($AST->getMonths(), $valueMap);
        $this->getCronCreator()->in($mappedMonths);
    }

    public function walkDetermineAtStatement(DetermineAtStatement $AST)
    {
        $times = $AST->getTimes();
        $mappedTimes = array();
        foreach ($times as $time) {
            preg_match('/(?<int>\d+)(?<meridiem>\w+)/i', $time, $matches);
            $mappedTimes[] = $matches['int'] + ($matches['meridiem'] == 'pm' ? 12 : 0);
        }

        $this->getCronCreator()->at($mappedTimes);
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
        $this->getCronCreator()->on($mappedDays);
    }

    public function walkDetermineEveryStatement(DetermineEveryStatement $AST)
    {
        $this->getCronCreator()->every($AST->getAmount(), $AST->getFrequency());
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