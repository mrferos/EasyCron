<?php
namespace EasyCron\ECLang\AST;

class DetermineAtStatement extends Node
{
    /**
     * @var array
     */
    protected $_times = array();

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->_times;
    }

    /**
     * @param array $times
     */
    public function setTimes(array $times)
    {
        $this->_times = $times;
    }


}