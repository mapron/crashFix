<?php

class BinaryFile extends CDataProvider
{
    
    protected $_folders = array();
    protected $_folderPath;
	
    /**
     * Fetches the data from the persistent data storage.
     * @return array list of data items
     */
    protected function fetchData()
    {
        return $this->_folders;
    }
    /**
     * Fetches the data item keys from the persistent data storage.
     * @return array list of data item keys.
     */
    protected function fetchKeys()
    {
        return ['name', 'szie'];
    }
    /**
     * Calculates the total number of data items.
     * @return integer the total number of data items.
     */
    protected function calculateTotalItemCount()
    {
        return count($this->_folders);
    }
    
    
    
    public function __construct($folderPath)
    {
        $this->_folderPath = $folderPath;
        if (!file_exists($folderPath))
            mkdir($folderPath, 0777, true);
        
        $folder=opendir($folderPath);
        if($folder===false)
            throw new Exception('Unable to open directory: ' . $folderPath);

        while(($file=readdir($folder))!==false)
        {
            if($file==='.' || $file==='..')
                continue;
            
            $path=$folderPath.DIRECTORY_SEPARATOR.$file;
           
            if(is_dir($path))
            {
                $item = ['name' => basename($path)];
                
                $subFiles = CFileHelper::findFiles($path);
                $size = 0;
                foreach ($subFiles as $file) 
                    $size += filesize($file);
                $item['size'] = $size;
                $this->_folders[] = $item;
            }                    
        }
        closedir($folder);       
    }
    
    public function getPath($subfolder = null)
    {
        return $subfolder ? $this->_folderPath . DIRECTORY_SEPARATOR . $subfolder : $this->_folderPath;
    }
    
    public function delete($subfolder)
    {
        CFileHelper::removeDirectory( $this->_folderPath . DIRECTORY_SEPARATOR . $subfolder);
    }
    
    // make folder with binary files flat, without recursive folders.
    public function renameFilesRecursive($subfolder)
    {
        $folderPath = $this->getPath($subfolder);
        $folder=opendir($folderPath);
        if($folder===false)
            throw new Exception('Unable to open directory: ' . $folderPath);
            
        while(($file=readdir($folder))!==false)
        {
            if($file==='.' || $file==='..')
                continue;
                
            $path=$folderPath.DIRECTORY_SEPARATOR.$file;
            
            if(is_dir($path))
            {                
                $subFiles = CFileHelper::findFiles($path);

                foreach ($subFiles as $subfile)
                    rename($subfile, $folderPath . DIRECTORY_SEPARATOR . basename($subfile) );
                
                CFileHelper::removeDirectory( $path );
            }
        }
        closedir($folder); 
    }
}