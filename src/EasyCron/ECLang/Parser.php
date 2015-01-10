<?php
namespace EasyCron\ECLang;

use EasyCron\ECLang\AST\DetermineAtStatement;
use EasyCron\ECLang\AST\DetermineEveryStatement;
use EasyCron\ECLang\AST\DetermineInStatement;
use EasyCron\ECLang\AST\DetermineOnStatement;
use EasyCron\ECLang\AST\DetermineOnTheStatement;
use EasyCron\ECLang\AST\ExecuteStatement;
use EasyCron\ECLang\AST\Node;

class Parser {
    /** @var  Lexer */
    protected $_lexer;

    /**
     * @param Lexer $lexer
     */
    public function __construct(Lexer $lexer)
    {
        $this->_lexer = $lexer;
    }

    /**
     * Parse an ECLang string
     *
     * @return string
     */
    public function parse()
    {
        $AST = $this->eclLang();
        $walker = new ECWalker();
        $results = $walker->walk($AST);
        return $results;
    }

    /**
     * Takes an ECLang string and breaks it down into an AST
     *
     * @return Node[]
     * @throws ParseException
     */
    public function eclLang()
    {
        $this->_lexer->moveNext();
        if ($this->_lexer->lookahead['type'] != Lexer::T_DETERMINER) {
            $this->syntaxError($this->_lexer->getLiteral(Lexer::T_DETERMINER));
        }

        $statements = [];

        while (!is_null($this->_lexer->lookahead)) {
            switch ($this->_lexer->lookahead['type']) {
                case Lexer::T_DETERMINER:
                    $statements[] = $this->determineStatement();
                    break;
                case Lexer::T_COMMANDPRECURSOR;
                    $statements[] = $this->executeStatement();
                    break;
                default:
                    var_dump($this->_lexer->lookahead); die;
                    $this->syntaxError(Lexer::T_DETERMINER);
                    break;
            }
        }

        return $statements;
    }

    /**
     * The determiner is really one of many statements. Decide how to handle it here.
     *
     * @todo break the determiner out into more tokens
     * @return DetermineAtStatement|DetermineEveryStatement|DetermineInStatement|DetermineOnStatement|DetermineOnTheStatement
     */
    public function determineStatement()
    {
        $this->match(Lexer::T_DETERMINER);

        switch ($this->_lexer->token['value']) {
            case 'every':
                return $this->everyStatement();
            case 'on the':
                return $this->onTheStatement();
            case 'at':
                return $this->atStatement();
            case 'on':
                return $this->onStatement();
            case 'in':
                return $this->inStatement();
        }
    }

    /**
     * Handle "on the" statement
     *
     * @return DetermineOnTheStatement
     * @throws ParseException
     */
    public function onTheStatement()
    {
        $determineStatement = new DetermineOnTheStatement();
        $nextToken = $this->_lexer->lookahead;
        if ($nextToken['type'] == Lexer::T_INTEGER) {
            $this->match(Lexer::T_INTEGER);
            $determineStatement->setDays(array($this->_lexer->token['value']));
        }elseif ($nextToken['type'] == Lexer::T_DAYUNIT) {
            $this->match(Lexer::T_DAYUNIT);
            $determineStatement->setDays(explode(',', $this->_lexer->token['value']));
        } else {
            $this->syntaxError(Lexer::T_DAYUNIT);
        }

        return $determineStatement;
    }

    /**
     * Handle "execute" statement
     *
     * @return ExecuteStatement
     */
    public function executeStatement()
    {
        $this->match(Lexer::T_COMMANDPRECURSOR);
        $executeStatement = new ExecuteStatement();
        $this->match(Lexer::T_STRING);
        $commandString = $this->_lexer->token['value'];

        $executeStatement->setCommand($commandString);
        return $executeStatement;
    }

    /**
     * Handle "every" statement
     *
     * @return DetermineEveryStatement
     * @throws ParseException
     */
    public function everyStatement()
    {
        $determineStatement = new DetermineEveryStatement();
        $nextToken = $this->_lexer->lookahead;
        $amount = null;
        $frequency = null;
        if ($nextToken['type'] == Lexer::T_INTEGER) {
            $this->match(Lexer::T_INTEGER);
            $amountToken = $this->_lexer->token;

            $this->match(Lexer::T_TIMEUNIT);
            $timeUnit = $this->_lexer->token;

            $amount    = $amountToken['value'];
            $frequency = $timeUnit['value'];
        } elseif ($nextToken['type'] == Lexer::T_TIMEUNIT) {
            $amount     = 1;
            $frequency  = $nextToken['value'];
        } else {
            $this->syntaxError(Lexer::T_INTEGER, $nextToken);
        }

        $determineStatement->setFrequency($frequency);
        $determineStatement->setAmount($amount);

        return $determineStatement;
    }

    /**
     * Handle "at" statement
     *
     * @return DetermineAtStatement
     */
    public function atStatement()
    {
        $determineStatement = new DetermineAtStatement();

        $collectedValues = $this->_collectIntervals(array(Lexer::T_TIMESERIES));
        $determineStatement->setTimes($collectedValues[Lexer::T_TIMESERIES]);

        return $determineStatement;
    }

    /**
     * Handle "on" statement
     *
     * @return DetermineOnStatement
     */
    public function onStatement()
    {
        $determineStatement = new DetermineOnStatement();

        $collectedValues = $this->_collectIntervals(array(Lexer::T_WEEKUNIT));
        $determineStatement->setDays($collectedValues[Lexer::T_WEEKUNIT]);

        return $determineStatement;
    }

    /**
     * Handle "in" statement
     *
     * @return DetermineInStatement
     */
    public function inStatement()
    {
        $determineStatement = new DetermineInStatement();

        $collectedValues = $this->_collectIntervals(array(Lexer::T_MONTHUNIT));
        $determineStatement->setMonths($collectedValues[Lexer::T_MONTHUNIT]);

        return $determineStatement;
    }

    /**
     * Many of the determiners are followed by interval groups. Collect those values.
     *
     * @param array $tokensToMatch
     * @return array
     */
    protected function _collectIntervals(array $tokensToMatch)
    {
        $values = array();
        foreach ($tokensToMatch as $tokenToMatch) {
            $values[$tokenToMatch] = array();
            do {
                $this->match($tokenToMatch);
                $values[$tokenToMatch][] = rtrim($this->_lexer->token['value'], ',');
            }while ($this->_lexer->lookahead['type'] == $tokenToMatch);
        }

        return $values;
    }

    /**
     * Match a given token and move up lexer to that point
     *
     * @param $token
     * @throws ParseException
     */
    public function match($token)
    {
        $lookaheadType = $this->_lexer->lookahead['type'];

        // short-circuit on first condition, usually types match
        if ($lookaheadType !== $token) {
            $this->syntaxError($this->_lexer->getLiteral($token));
        }

        $this->_lexer->moveNext();
    }

    /**
     * Emit a syntax error
     *
     * @param string $expected
     * @param null $token
     * @throws ParseException
     */
    public function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->_lexer->lookahead;
        }

        $tokenPos = (isset($token['position'])) ? $token['position'] : '-1';

        $message  = "line 0, col {$tokenPos}: Error: ";
        $message .= ($expected !== '') ? "Expected {$expected}, got " : 'Unexpected ';
        $message .= ($this->_lexer->lookahead === null) ? 'end of string.' : "'{$token['value']}'";

        throw ParseException::syntaxError($message);
    }
}