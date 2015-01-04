<?php
namespace EasyCron\ECLang\AST;

class DetermineEveryStatement extends Node
{
    /**
     * unit of time
     *
     * @var string
     */
    protected $_frequency;
    /**
     * Amount of unit
     *
     * @var
     */
    protected $_amount;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->_amount = $amount;
    }

    /**
     * @return string
     */
    public function getFrequency()
    {
        return $this->_frequency;
    }

    /**
     * @param string $frequency
     */
    public function setFrequency($frequency)
    {
        $this->_frequency = $frequency;
    }
}