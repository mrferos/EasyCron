<?php
namespace EasyCronTest\Cron;

use \EasyCron\Cron\Creator as CronCreator;

class CreatorTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $cronCreator = new CronCreator();
        $this->assertEquals('* * * * * *', (string)$cronCreator);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEveryInvalidUnit()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'dogs');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEveryNonNumericAmount()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every('foo', 'minute');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSmallerThan1Amount()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(0, 'minute');
    }

    public function testEveryMinute()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'minute');
        $cronCreator->execute('foo');
        $this->assertEquals('* * * * * foo', $cronCreator->getLine());
    }

    public function testEvery2Minute()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'minute');
        $cronCreator->execute('foo');
        $this->assertEquals('*/2 * * * * foo', $cronCreator->getLine());
    }

    public function testEveryHour()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'hour');
        $cronCreator->execute('foo');
        $this->assertEquals('0 * * * * foo', $cronCreator->getLine());
    }

    public function testEvery2Hour()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'hour');
        $cronCreator->execute('foo');
        $this->assertEquals('0 */2 * * * foo', $cronCreator->getLine());
    }

    public function testEveryDay()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'day');
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 * * * foo', $cronCreator->getLine());
    }

    public function testEveryWeek()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'week');
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 * * * foo', $cronCreator->getLine());
    }

    public function testEvery2Week()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'week');
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 * * * test $(($(date +\%W)\%2)) -eq 1 && foo', $cronCreator->getLine());
    }

    public function testEveryMonth()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'month');
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 0 * * foo', $cronCreator->getLine());
    }

    public function testEvery2Month()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'month');
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 0 * * test $(($(date +\%m)\%2)) -eq 1 && foo', $cronCreator->getLine());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testOnTheStringValuesInDay()
    {
        $cronCreator = new CronCreator();
        $cronCreator->onThe(array('rawr'));
    }

    /**
     * @dataProvider getDays
     */
    public function testOnDays($day)
    {
        $cronCreator = new CronCreator();
        $cronCreator->onThe(array($day));
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 '. $day .' * * foo', $cronCreator->getLine());
    }

    /**
     * @dataProvider getMonths
     */
    public function testInMonths($month, $monthNum)
    {
        $cronCreator = new CronCreator();
        $cronCreator->in(array($month));
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 1 '. $monthNum .' * foo', $cronCreator->getLine());
    }

    /**
     * @dataProvider getHours
     */
    public function testAtHours($hour)
    {
        $cronCreator = new CronCreator();
        $cronCreator->at(array($hour));
        $cronCreator->execute('foo');
        $this->assertEquals('0 ' . $hour . ' * * * foo', $cronCreator->getLine());
    }

    /**
     * @dataProvider getWeekdays
     */
    public function testOnWeekdays($weekday, $weekNum)
    {
        $cronCreator = new CronCreator();
        $cronCreator->on(array($weekday));
        $cronCreator->execute('foo');
        $this->assertEquals('0 0 * * '. $weekNum .' foo', $cronCreator->getLine());
    }

    public function testEveryMinuteOnSpecificDays()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'minute');
        $cronCreator->onThe(array(1 , 3, 4));
        $cronCreator->execute('foo');
        $this->assertEquals('* * 1,3,4 * * foo', $cronCreator->getLine());
    }

    public function testEvery2MinutesOnSpecificDays()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'minute');
        $cronCreator->onThe(array(1 , 3, 4));
        $cronCreator->execute('foo');
        $this->assertEquals('*/2 * 1,3,4 * * foo', $cronCreator->getLine());
    }

    public function testEveryMinuteDuringSpecificHours()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(1, 'minute');
        $cronCreator->at(array(0, 3, 5, 13));
        $cronCreator->execute('foo');
        $this->assertEquals('* 0,3,5,13 * * * foo', $cronCreator->getLine());
    }

    public function testEveryMinute2DuringSpecificHours()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'minute');
        $cronCreator->at(array(0, 3, 5, 13));
        $cronCreator->execute('foo');
        $this->assertEquals('*/2 0,3,5,13 * * * foo', $cronCreator->getLine());
    }

    public function testEvery2MinutesInSpecificMonths()
    {
        $cronCreator = new CronCreator();
        $cronCreator->every(2, 'minute');
        $cronCreator->in(array('october', 'july'));
        $cronCreator->execute('foo');
        $this->assertEquals('*/2 * * 10,7 * foo', $cronCreator->getLine());
    }

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

    /**
     * @expectedException RuntimeException
     */
    public function testAtInvalidTimeRanges()
    {
        $cronCreator = new CronCreator();
        $cronCreator->at(range(1, 200));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testStringInTimeRange()
    {
        $cronCreator = new CronCreator();
        $cronCreator->at(array('foo'));
    }

    public function getAcceptableConvertMethod()
    {
        $cronCreator = new CronCreator();
        $reflObj = new \ReflectionObject($cronCreator);
        $convertMethod = $reflObj->getMethod('_convert');
        $convertMethod->setAccessible(true);

        return array($cronCreator, $convertMethod);
    }

    public function getMonths()
    {
        return array(
            array('january', 1),
            array('february', 2),
            array('march', 3),
            array('april', 4),
            array('may', 5),
            array('june', 6),
            array('july', 7),
            array('august', 8),
            array('september', 9),
            array('october', 10),
            array('november', 11),
            array('december', 12),
        );
    }

    public function getHours()
    {
        return array(
            array(1),
            array(2),
            array(3),
            array(4),
            array(5),
            array(6),
            array(7),
            array(8),
            array(9),
            array(10),
            array(11),
            array(13),
            array(14),
            array(15),
            array(16),
            array(17),
            array(18),
            array(19),
            array(20),
            array(21),
            array(22),
            array(23),
            array(24)
        );
    }

    public function getDays()
    {
        return array(
            array(1),
            array(2),
            array(3),
            array(4),
            array(5),
            array(6),
            array(7),
            array(8),
            array(9),
            array(10),
            array(11),
            array(13),
            array(14),
            array(15),
            array(16),
            array(17),
            array(18),
            array(19),
            array(20),
            array(21),
            array(22),
            array(23),
            array(24),
            array(25),
            array(26),
            array(27),
            array(28),
            array(29),
            array(30),
            array(31),
        );
    }

    public function getWeekdays()
    {
        return array(
            array('monday', 0),
            array('tuesday', 1),
            array('wednesday', 2),
            array('thursday', 3),
            array('friday', 4),
            array('saturday', 5),
            array('sunday', 6)
        );
    }
}