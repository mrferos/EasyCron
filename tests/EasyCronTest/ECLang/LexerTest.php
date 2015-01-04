<?php
namespace EasyCronTest\ECLang;

use EasyCron\ECLang\Lexer;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    public function testLexerParsesSimpleStatementCorrectly()
    {
        $statement = 'every 2 minutes execute "foo"';
        $tokens = array(
            array(
                'value'    => 'every',
                'type'     => Lexer::T_DETERMINER,
                'position' => 0
            ),
            array(
                'value'    => '2',
                'type'     => Lexer::T_INTEGER,
                'position' => 6
            ),
            array(
                'value'    => 'minutes',
                'type'     => Lexer::T_TIMEUNIT,
                'position' => 8
            ),
            array(
                'value'    => 'execute',
                'type'     => Lexer::T_COMMANDPRECURSOR,
                'position' => 16
            ),
            array(
                'value'    => '"foo"',
                'type'     => Lexer::T_STRING,
                'position' => 24
            ),
        );
        $lexer = new Lexer();
        $lexer->setInput($statement);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $actual = $lexer->lookahead;
            $this->assertEquals($expected['value'], $actual['value']);
            $this->assertEquals($expected['type'], $actual['type']);
            $this->assertEquals($expected['position'], $actual['position']);
        }

        $this->assertFalse($lexer->moveNext());
    }
}