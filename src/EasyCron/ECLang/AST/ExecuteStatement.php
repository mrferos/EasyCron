<?php
namespace EasyCron\ECLang\AST;

class ExecuteStatement extends Node
{
    protected $_command;

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->_command = $command;
    }


}