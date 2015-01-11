<?php
namespace EasyCron\Cron;

class Writer implements WriterInterface
{
    const MODE_INSERT  = 'insert';
    const MODE_REPLACE = 'replace';
    const MODE_UPSERT  = 'upsert';

    /**
     * Where the cronjob is located
     *
     * @var string
     */
    protected $_cronjobFile;

    /**
     * Mode for file writing
     *
     * @var string
     */
    protected $_mode;

    /**
     * @return string
     */
    public function getCronjobFile()
    {
        return $this->_cronjobFile;
    }

    /**
     * @param string $cronjobFile
     */
    public function setCronjobFile($cronjobFile)
    {
        $this->_cronjobFile = $cronjobFile;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        if (empty($this->_mode)) {
            $this->setMode(self::MODE_UPSERT);
        }

        return $this->_mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        if (!is_string($mode)) {
            throw new \InvalidArgumentException('$mode MUST be a string');
        }

        $this->_mode = $mode;
    }

    /**
     * Given an array of cronjob lines, write them to the specified
     * cronjob line. Possibilities given the modes:
     *
     * * insert: will append to the end of the file
     * * replace: will replace the contents of the file with the specifies cronlines
     * * upsert: will see if any of the specified cronjobs are already in the file and does not set them.
     * Only new cronjobs are set.
     *
     * @param array $cronjobs
     */
    public function write(array $cronjobs)
    {
        $cronjobFile = $this->getCronjobFile();
        switch ($this->getMode()) {
            case 'insert':
                file_put_contents($cronjobFile, "\n" . implode("\n", $cronjobs), FILE_APPEND);
                break;
            case 'replace':
                file_put_contents($cronjobFile, "\n" . implode("\n", $cronjobs));
                break;
            case 'upsert':
                $preExistingCronJobs = $this->read();
                if (!empty($preExistingCronJobs)) {
                    foreach ($preExistingCronJobs as $pJob) {
                        if(in_array($pJob, $cronjobs)) {
                            unset($cronjobs[array_search($pJob, $cronjobs)]);
                        }
                    }

                }

                if (!empty($cronjobs)) {
                    file_put_contents($cronjobFile, "\n" . implode("\n", $cronjobs), FILE_APPEND);
                }

                break;
            default:
                throw new \RuntimeException('Unknown mode: ' . $this->getMode());
        }
    }

    /**
     * Read the cronjob file and return the lines
     *
     * @return array
     */
    public function read()
    {
        if (file_exists($this->_cronjobFile)) {
            return array_map('trim', file($this->_cronjobFile));
        }
    }
}