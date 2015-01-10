<?php
namespace EasyCron;

use EasyCron\Traits\SetOptions;

class Settings
{
    use SetOptions;

    /**
     * @var FileSystem
     */
    protected $_fileSystem;
    /**
     * @var string
     */
    protected $_settingsFile;

    /**
     * @var string
     */
    protected $_preferredEditor;

    public function __construct(FileSystem $fs, $settingsFile = null)
    {
        $this->setFileSystem($fs);
        if (empty($settingsFile)) {
            $settingsFile = $fs->getStorageDir() . '/.ec-settings.json';
            if (file_exists($settingsFile)) {
                $this->load($settingsFile);
            }
        }

        $this->_settingsFile = $settingsFile;
    }

    public function write($settingsFile = null)
    {
        $settingsFile = is_null($settingsFile) ? $this->_settingsFile : $settingsFile;
        if (empty($settingsFile)) {
            throw new \RuntimeException('$settingsFile must not be null');
        }

        file_put_contents($settingsFile, json_encode(array(
            'preferred_editor' => $this->getPreferredEditor()
        ), JSON_PRETTY_PRINT));
    }

    public function load($settingsFile)
    {
        if (!file_exists($settingsFile)) {
            throw new \RuntimeException("'$settingsFile' does not exists");
        }

        $json = json_decode(file_get_contents($settingsFile), true);
        if (false !== $json) {
            $this->setOptions($json);
        }
    }

    /**
     * @return string
     */
    public function getPreferredEditor()
    {
        return $this->_preferredEditor;
    }

    /**
     * @param string $preferredEditor
     */
    public function setPreferredEditor($preferredEditor)
    {
        $this->_preferredEditor = $preferredEditor;
    }

    /**
     * @return FileSystem
     */
    public function getFileSystem()
    {
        if (empty($this->_fileSystem)) {
            throw new \RuntimeException('The file system has not been set');
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