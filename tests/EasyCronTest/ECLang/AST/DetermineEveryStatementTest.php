<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\DetermineEveryStatement;

class DetermineEveryStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testFrequencySetterAndGetter()
    {
        $frequency = 'minutes';
        $ds = new DetermineEveryStatement();
        $ds->setFrequency($frequency);

        $this->assertEquals($frequency, $ds->getFrequency());
    }

    public function testAmountSetterAndGetter()
    {
        $amount = 10;
        $ds = new DetermineEveryStatement();
        $ds->setAmount($amount);

        $this->assertEquals($amount, $ds->getAmount());
    }
}