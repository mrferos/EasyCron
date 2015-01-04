<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\DetermineOnStatement;

class DetermineOnStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testSetterAndGetter()
    {
        $values = array('mon', 'wed', 'thursday');
        $ds = new DetermineOnStatement();
        $ds->setDays($values);
        $returnedValues = $ds->getDays();
        foreach ($values as $value) {
            $this->assertContains($value, $returnedValues);
        }
    }
}