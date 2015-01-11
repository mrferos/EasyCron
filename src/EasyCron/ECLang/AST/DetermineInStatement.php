<?php
namespace EasyCron\ECLang\AST;

class DetermineInStatement extends Node
{
    /**
     * @var array
     */
    protected $_months = array();

    /**
     * @return array
     */
    public function getMonths()
    {
        return $this->_months;
    }

    /**
     * @param array $months
     */
    public function setMonths(array $months)
    {
        $this->_months = $months;
    }


}