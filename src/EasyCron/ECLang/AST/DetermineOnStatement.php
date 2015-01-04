<?php
namespace EasyCron\ECLang\AST;

class DetermineOnStatement extends Node
{
    /**
     * @var array
     */
    protected $_days = array();

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->_days;
    }

    /**
     * @param array $days
     */
    public function setDays($days)
    {
        $this->_days = $days;
    }


}