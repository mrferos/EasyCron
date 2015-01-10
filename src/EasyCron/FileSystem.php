<?php
namespace EasyCron;

use EasyCron\Traits\SetOptions;

class FileSystem
{
    use SetOptions;

    protected $_storageDir;

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @return mixed
     */
    public function getStorageDir()
    {
        if (empty($this->_storageDir)) {
            $userSettings = posix_getpwuid(posix_getuid());
            $this->setStorageDir($userSettings['dir']);
        }

        return $this->_storageDir;
    }

    /**
     * @param string $storageDir
     */
    public function setStorageDir($storageDir)
    {
        if (!is_dir($storageDir) || !is_writable($storageDir))
        {
            throw new \RuntimeException('$storageDir is not writable');
        }

        $this->_storageDir = $storageDir;
    }


}