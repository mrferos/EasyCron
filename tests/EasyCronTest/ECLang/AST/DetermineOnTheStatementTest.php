<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\DetermineOnTheStatement;

class DetermineOnTheStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testSetterAndGetter()
    {
        $values = array(4, 15, 30);
        $ds = new DetermineOnTheStatement();
        $ds->setDays($values);
        $returnedValues = $ds->getDays();
        foreach ($values as $value) {
            $this->assertContains($value, $returnedValues);
        }
    }
}