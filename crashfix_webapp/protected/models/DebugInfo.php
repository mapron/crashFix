<?php

class DebugInfo extends CDataProvider
{

    protected $_root;
    protected $_folders = array();
    protected $_archList = ['Win64_PDB', 'Win32_PDB'];
    protected $_totalSize = 0;

    protected function fetchData()
    {
        return $this->_folders;
    }

    protected function fetchKeys()
    {
        return ['project', 'version', 'arch', 'size'];
    }

    protected function calculateTotalItemCount()
    {
        return count($this->_folders);
    }

    public function __construct($root, $projectName, $versionList)
    {
        $this->_root = $root;
        foreach ($versionList as $key => $version)
        {
            if ($key <= 0 || $version == "(not set)") continue;
            foreach ($this->_archList as $arch)
            {
                $path = $this->getPath($projectName, $version, $arch);

                $item = ['project' => $projectName, 'version'=> $version, 'arch'=> $arch];

                $subFiles = file_exists($path) ? CFileHelper::findFiles($path) : [];
                $size = 0;
                foreach ($subFiles as $file)
                    $size += filesize($file);

                $item['size'] = $size;
                $this->_totalSize += $size;
                $this->_folders[] = $item;
            }
        }
    }

    public function getTotalSize()
    {
        return $this->_totalSize;
    }

    public function getPath($projectName, $version, $arch)
    {
        return $this->_root.DIRECTORY_SEPARATOR.$projectName.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$arch;
    }

    public function delete($projectName, $version, $arch)
    {
        $path = $this->getPath($projectName, $version, $arch);
        CFileHelper::removeDirectory($path);
    }
}