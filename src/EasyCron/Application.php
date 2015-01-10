<?php
namespace EasyCron;

use EasyCron\Console\Command\EditCommand;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @var FileSystem
     */
    protected $_fileSystem;
    /**
     * @var Settings
     */
    protected $_settings;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('EasyCron', 'V1.0');

        $editCommand = new EditCommand();
        $this->add($editCommand);
        $this->setDefaultCommand($editCommand->getName());

    }

    /**
     * @return FileSystem
     */
    public function getFileSystem()
    {
        if (empty($this->_fileSystem)) {
            $this->setFileSystem(new FileSystem());
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

    /**
     * @return Settings
     */
    public function getSettings()
    {
        if (empty($this->_settings)) {
            $this->setSettings(new Settings($this->getFileSystem()));
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
}