<?php
namespace EasyCron\Console\Command;

use EasyCron\FileSystem;
use EasyCron\Settings;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var Settings
     */
    protected $_settings;
    /**
     * @var FileSystem
     */
    protected $_fileSystem;

    /**
     * @return Settings
     */
    public function getSettings()
    {
        if (empty($this->_settings)) {
            $this->setSettings($this->getApplication()->getSettings());
        }

        return $this->_settings;
    }

    /**
     * @param Settings $settings
     */
    public function setSettings(Settings $settings)
    {
        $this->_settings = $settings;
    }

    /**
     * @return FileSystem
     */
    public function getFileSystem()
    {
        if (empty($this->_fileSystem)) {
            $this->setFileSystem($this->getSettings()->getFileSystem());
        }

        return $this->_fileSystem;
    }

    /**
     * @param FileSystem $fileSystem
     */
    public function setFileSystem(FileSystem $fileSystem)
    {
        $this->_fileSystem = $fileSystem;
    }


}