<?php
namespace EasyCron\ECLang;

use Doctrine\Common\Lexer\AbstractLexer;

class Lexer extends AbstractLexer {

    const T_NONE                = 1;
    const T_INTEGER             = 2;
    const T_STRING              = 3;
    const T_IDENTIFIER          = 4;

    const T_DETERMINER          = 101;
    const T_TIMESERIES          = 102;
    const T_TIMEUNIT            = 103;
    const T_MONTHUNIT           = 104;
    const T_WEEKUNIT            = 105;
    const T_COMMANDPRECURSOR    = 106;
    const T_DAYUNIT             = 107;


    protected $_regexes = array(
        self::T_DETERMINER => '\b(?:every|at|on the|on|in)\b',
        self::T_MONTHUNIT  => '(?:(?:Jan(?:urary)?|Feb(?:urary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)(?:,)?)',
        self::T_TIMESERIES => '(?:\d+(?:am|pm)(?:,)?)',
        self::T_WEEKUNIT   => '(?:(?:mon|tues|wed(?:nes)?|thurs|fri)(?:day)?(?:s)?(?:,)?)',
        self::T_TIMEUNIT   => '(?:minute|day|hour|month|year|week)(?:s)?',
        self::T_DAYUNIT    => '\d(?:,)?',
        self::T_STRING     => '\"(?:[^"\\\\]|\\\\.)*\"',
    );


    /**
     * Lexical catchable patterns.
     *
     * @return array
     */
    protected function getCatchablePatterns()
    {
        return array_slice(array_values($this->_regexes), 0, 5);
    }

    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected function getNonCatchablePatterns()
    {
        return array('#(?:\s+)?.*$', '\s+');
    }

    /**
     * Retrieve token type. Also processes the token value if necessary.
     *
     * @param string $value
     *
     * @return integer
     */
    protected function getType(&$value)
    {
        if (is_numeric($value)) {
            return self::T_INTEGER;
        }

        foreach ($this->_regexes as $token => $regex) {
            if (preg_match('/('.$regex.')/i', $value)) {
                return $token;
            }
        }

        if (trim($value) == 'execute') {
            return self::T_COMMANDPRECURSOR;
        }

        return self::T_NONE;
    }

}