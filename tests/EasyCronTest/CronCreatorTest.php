<?php
namespace EasyCronTest;

use EasyCron\CronCreator;

class CronCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testConvertLargeAcceptableRangeArray()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, range(1, 100), array('monday', 'tuesday'), array(0, 1));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConvertAcceptableRangeWithNonIntValues()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, array('foo', 'rawr'), array('monday', 'tuesday'), array(0, 1));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConvertAcceptableRangeWithSmallArray()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, array(5), array('monday', 'tuesday'), array(0, 1));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConvertAcceptableRangeWithUnexpectedKeys()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, array('foo'=>0, 'rawr'=>1), array('monday', 'tuesday'), array(0, 1));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConvertValuesThatAreNotStringOrInt()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, array(0, 1), array('monday', 'tuesday'), array(new \stdClass()));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConvertValueOutOfAcceptableRange()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, array(0, 1), array('monday', 'tuesday'), array(5));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConvertValueNotInValidValues()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertMethod->invoke($cronCreator, array(0, 1), array('monday', 'tuesday'), array('wednesday'));
    }

    public function testConvertWithStringValues()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertedValues = $convertMethod->invoke(
            $cronCreator,
            array(0, 6),
            array('monday', 'tuesday', 'wednesday', 'friday'),
            array('wednesday', 'monday')
        );

        $this->assertCount(2, $convertedValues);
        $this->assertArrayHasKey(0, $convertedValues);
        $this->assertArrayHasKey(1, $convertedValues);
        $this->assertEquals(2, $convertedValues[0]);
        $this->assertEquals(0, $convertedValues[1]);
    }

    public function testConvertWithIntValues()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertedValues = $convertMethod->invoke(
            $cronCreator,
            array(0, 6),
            array('monday', 'tuesday', 'wednesday', 'friday'),
            array(3, 1, 2)
        );

        $this->assertCount(3, $convertedValues);
        $this->assertArrayHasKey(0, $convertedValues);
        $this->assertArrayHasKey(1, $convertedValues);
        $this->assertArrayHasKey(2, $convertedValues);
        $this->assertEquals(3, $convertedValues[0]);
        $this->assertEquals(1, $convertedValues[1]);
        $this->assertEquals(2, $convertedValues[2]);
    }

    public function testConvertWithMixedValues()
    {
        list($cronCreator, $convertMethod) = $this->getAcceptableConvertMethod();
        $convertedValues = $convertMethod->invoke(
            $cronCreator,
            array(0, 6),
            array('monday', 'tuesday', 'wednesday', 'friday'),
            array(3, 'tuesday', 2)
        );

        $this->assertCount(3, $convertedValues);
        $this->assertArrayHasKey(0, $convertedValues);
        $this->assertArrayHasKey(1, $convertedValues);
        $this->assertArrayHasKey(2, $convertedValues);
        $this->assertEquals(3, $convertedValues[0]);
        $this->assertEquals(1, $convertedValues[1]);
        $this->assertEquals(2, $convertedValues[2]);
    }

    public function getAcceptableConvertMethod()
    {
        $cronCreator = new CronCreator();
        $reflObj = new \ReflectionObject($cronCreator);
        $convertMethod = $reflObj->getMethod('_convert');
        $convertMethod->setAccessible(true);

        return array($cronCreator, $convertMethod);
    }
}