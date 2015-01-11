<?php
namespace EasyCronTest\Cron;

use EasyCron\Cron\Writer as CronWriter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('writer_test_dir');
    }

    public function testSetCronJobFile()
    {
        $writer = new CronWriter();
        $writer->setCronjobFile('test');
        $reflObject = new \ReflectionObject($writer);
        $fileProp = $reflObject->getProperty('_cronjobFile');
        $fileProp->setAccessible(true);
        $this->assertEquals('test', $fileProp->getValue($writer));
    }

    /**
     * @depends testSetCronJobFile
     */
    public function testGetCronJobFile()
    {
        $writer = new CronWriter();
        $writer->setCronjobFile('test');
        $this->assertEquals('test', $writer->getCronjobFile());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNonStringMode()
    {
        $writer = new CronWriter();
        $writer->setMode(array());
    }

    public function testSetMode()
    {
        $writer = new CronWriter();
        $writer->setMode(CronWriter::MODE_INSERT);
        $reflObject = new \ReflectionObject($writer);
        $modeProp = $reflObject->getProperty('_mode');
        $modeProp->setAccessible(true);
        $this->assertEquals(CronWriter::MODE_INSERT, $modeProp->getValue($writer));
    }

    /**
     * @depends testSetMode
     */
    public function testGetMode()
    {
        $writer = new CronWriter();
        $writer->setMode(CronWriter::MODE_INSERT);
        $this->assertEquals(CronWriter::MODE_INSERT, $writer->getMode());
    }

    public function testGetModeWithNoValuePreviouslySet()
    {
        $writer = new CronWriter();
        $this->assertEquals(CronWriter::MODE_UPSERT, $writer->getMode());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWriteInvalidMode()
    {
        $writer = new CronWriter();
        $writer->setMode("FOO_RAWR");
        $writer->write(array());
    }

    public function testWriteInsert()
    {
        $cronLines = array(
            '* * * * * foo',
            '*/2 * * * * foo1',
            '* * 1 * * foo2',
            '* * * * * foo3'
        );

        $cronJobFile = vfsStream::url('writer_test_dir/cron_file');
        $writer = new CronWriter();
        $writer->setCronjobFile($cronJobFile);
        $writer->setMode(CronWriter::MODE_INSERT);
        $writer->write($cronLines);

        $cronLineContent = file_get_contents($cronJobFile);
        $this->assertEquals("\n" . implode("\n", $cronLines), $cronLineContent);
    }

    public function testWriteReplace()
    {
        $cronJobFile = vfsStream::url('writer_test_dir/cron_file');
        file_put_contents($cronJobFile, 'THIS IS SOME AWESOME TEXT');

        $cronLines = array(
            '* * * * * foo',
            '*/2 * * * * foo1',
            '* * 1 * * foo2',
            '* * * * * foo3'
        );

        $writer = new CronWriter();
        $writer->setCronjobFile($cronJobFile);
        $writer->setMode(CronWriter::MODE_REPLACE);
        $writer->write($cronLines);

        $cronLineContent = file_get_contents($cronJobFile);
        $this->assertEquals("\n" . implode("\n", $cronLines), $cronLineContent);
    }

    public function testWriteUpsertNoAddOnSameCronJob()
    {
        $cronJobFile = vfsStream::url('writer_test_dir/cron_file');
        $cronLines = array(
            '* * * * * foo',
            '*/2 * * * * foo1',
            '* * 1 * * foo2',
            '* * * * * foo3'
        );

        file_put_contents($cronJobFile, implode("\n", $cronLines));

        $writer = new CronWriter();
        $writer->setCronjobFile($cronJobFile);
        $writer->setMode(CronWriter::MODE_UPSERT);
        $writer->write($cronLines);

        $cronLineContent = file_get_contents($cronJobFile);
        $this->assertEquals(implode("\n", $cronLines), $cronLineContent);
    }

    public function testWriteUpsertAddDifferentCronJob()
    {
        $cronJobFile = vfsStream::url('writer_test_dir/cron_file');
        $cronLines = array(
            '* * * * * foo',
            '*/2 * * * * foo1',
            '* * 1 * * foo2',
            '* * * * * foo3'
        );

        file_put_contents($cronJobFile, implode("\n", $cronLines));

        $cronLines = array_merge($cronLines, array('* * * * * THIS IS A COOL TEST'));
        $writer = new CronWriter();
        $writer->setCronjobFile($cronJobFile);
        $writer->setMode(CronWriter::MODE_UPSERT);
        $writer->write($cronLines);

        $cronLineContent = file_get_contents($cronJobFile);
        $this->assertEquals(implode("\n", $cronLines), $cronLineContent);
    }
}