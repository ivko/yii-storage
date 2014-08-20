<?php
require_once 'YiiStorageServiceAbstract.php';

class YiiStorageServiceLocal extends YiiStorageServiceAbstract
{
    // General
    public $type = 'local';
    public $pathAlias = 'webroot.public';
    public $publicPathAlias = 'public';
    
    // Runtime
    public $path = null;
    public $publicPath = null;
    
    public function init() {
        parent::init();
        if ($this->path === null) {
            $this->path = Yii::getPathOfAlias($this->pathAlias);
        }
        if ($this->publicPath === null) {
            $this->publicPath = Yii::app()->getBaseUrl(true) . '/' . $this->publicPathAlias;
        }
    }
 
    public function getType()
    {
        return $this->type;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getPublicPath()
    {
        return $this->publicPath;
    }

    public function getPublicUrl(StorageFilesAbstract $model) {
        return $this->getPublicPath() . DS . $model->storage_path;
    }
    
    public function map(StorageFilesAbstract $model)
    {
        return $this->getPublicUrl($model);
    }

    public function getLocalPath(StorageFilesAbstract $model)
    {
        return $this->getPath() . DS . $model->storage_path;
    }

    public function store(StorageFilesAbstract $model, $file)
    {
        $path = $this->getScheme()->generate($model->toArray());
        // Copy file
        try
        {
            $this->_mkdir(dirname($this->getPath() . DS . $path));
            $this->_copy($file, $this->getPath() . DS . $path);
            @chmod($this->getPath() . DS . $path, 0777);
        }
        catch( Exception $e )
        {
            @unlink($this->getPath() . DS . $path);
            throw $e;
        }
        return $path;
    }

    public function read(StorageFilesAbstract $model)
    {
        $file = $this->getLocalPath($model);
        return @file_get_contents($file);
    }

    public function write(StorageFilesAbstract $model, $data)
    {
        // Write data
        $path = $this->getScheme()->generate($model->toArray());
  
        try
        {
            $this->_mkdir(dirname($this->getPath() . DS . $path));
            $this->_write($this->getPath() . DS . $path, $data);
            @chmod($path, 0777);
        }
  
        catch( Exception $e )
        {
            @unlink($this->getPath() . DS . $path);
            throw $e;
        }

        return $path;
    }

    public function remove(StorageFilesAbstract $model)
    {
        $storage_path = $model->storage_path;
        if (!empty($storage_path))
        {
           $this->removeFile($this->getPath() . DS . $storage_path);
        }
    }

    public function temporary(StorageFilesAbstract $model)
    {
        $file = $this->getPath() . DS . $model->storage_path;
        $tmp_file = $this->getPath() . DS . 'temporary'. DS . basename($file);
        $this->_copy($file, $tmp_file);
        @chmod($tmp_file, 0777);
        return $tmp_file;
    }

    public function removeFile($path)
    {
        $this->_delete($this->getPath() . DS . $path);
    }
}
