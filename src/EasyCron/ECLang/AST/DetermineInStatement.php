<?php
namespace EasyCron\ECLang\AST;

class DetermineInStatement extends Node
{
    /**
     * @var
     */
    protected $_months = array();

    /**
     * @return mixed
     */
    public function getMonths()
    {
        return $this->_months;
    }

    /**
     * @param mixed $months
     */
    public function setMonths($months)
    {
        $this->_months = $months;
    }


}