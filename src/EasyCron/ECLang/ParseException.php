<?php
namespace EasyCron\ECLang;

class ParseException extends \Exception
{
    public static function syntaxError($message, $previous = null)
    {
        return new self('[Syntax Error] ' . $message, 0, $previous);
    }
}