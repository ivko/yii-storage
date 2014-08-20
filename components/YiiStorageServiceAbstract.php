<?php
Yii::import('vendor.ivko.yii-storage.components.*');
Yii::import('vendor.ivko.yii-storage.components.scheme.*');
Yii::import('vendor.ivko.yii-storage.models.*');

abstract class YiiStorageServiceAbstract extends CApplicationComponent implements YiiStorageServiceInterface {

    public $schemeClass = 'YiiStorageServiceSchemeDynamic';
    public $modelClass = 'StorageFilesAbstract';
    
    protected $_identity;
    protected $_scheme;
    
    protected $_files = array();
    protected $_relationships = array();
    
    public function init() {
        parent::init();
        $this->_identity = 1;
    }

    public function getIdentity() {
        return $this->_identity;
    }

    public function getScheme() {
        if (null === $this->_scheme) {
            $class = $this->schemeClass;
            $this->_scheme = new $class();
        }
        return $this->_scheme;
    }

    public function setScheme(YiiStorageServiceSchemeInterface $scheme) {
        $this->_scheme = $scheme;
        return $this;
    }

    /* Utility */
    public function fileInfo($file) {
        // $file is an instance of Zend_Form_Element_File
        if ($file instanceof CUploadedFile) {
            $info = array(
                'tmp_name' => $file->getTempName(),
                'name' => $file->getName(),
                'type' => $file->getType(),
                'size' => $file->getSize(),
            );
        }
        // $file is a key of $_FILES
        else if (is_array($file)) {
            $info = $file;
        }
        // $file is a string
        else if (is_string($file)) {
            $info = array(
                'tmp_name' => $file,
                'name' => basename($file) ,
                'size' => filesize($file)
            );
            // Try to get image info
            if (function_exists('getimagesize') && ($imageinfo = getimagesize($file))) {
                $info['type'] = $imageinfo['mime'];
            }
            
            if (!isset($info['type'])) {
                $info['type'] = Apex_Toolbox_String::findMimeType($file);
            }
        }
        // $file is an unknown type
        else {
            throw new Exception('Unknown file type specified');
        }

        // Check to make sure file exists and not security problem
        self::_checkFile($info['tmp_name'], 04); // Check for read

        // Do some other stuff
        $mime_parts = explode('/', $info['type'], 2);
        $info['mime_major'] = $mime_parts[0];
        $info['mime_minor'] = $mime_parts[1];
        $info['hash'] = md5_file($info['tmp_name']);
        $info['extension'] = ltrim(strrchr($info['name'], '.') , '.');
        unset($info['type']);
        return $info;
    }

    protected function _removeScriptName($url) {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            // We can't do much now can we? (Well, we could parse out by ".")
            return $url;
        }
        
        if (($pos = strripos($url, basename($_SERVER['SCRIPT_NAME']))) !== false) {
            $url = substr($url, 0, $pos);
        }
        return $url;
    }

    protected function _checkFile($file, $mode = 06) {
        
        if ($mode && !file_exists($file)) {
            throw new Exception('File does not exist: ' . $file);
        }
        
        if (($mode & 04) && (!is_readable($file))) {
            throw new Exception('File not readable: ' . $file);
        }
        
        if (($mode & 02) && (!is_writable($file))) {
            throw new Exception('File not writeable: ' . $file);
        }
        
        if (($mode & 01) && (!is_executable($file))) {
            throw new Exception('File not executable: ' . $file);
        }
    }
    protected function _mkdir($path, $mode = 0777) {

        // Change umask
        if (function_exists('umask')) {
            $oldUmask = umask();
            umask(0);
        }

        // Change perms
        $code = 0;
        
        if (is_dir($path)) {
            @chmod($path, $mode);
        } elseif (!@mkdir($path, $mode, true)) {
            $code = 1;
        }

        // Revert umask
        if (function_exists('umask')) {
            umask($oldUmask);
        }

        // Respond
        if (1 == $code) {
            throw new Exception(sprintf('Could not create folder: %s', $path));
        }
    }
    protected function _move($from, $to) {

        // Change umask
        if (function_exists('umask')) {
            $oldUmask = umask();
            umask(0);
        }

        // Move
        $code = 0;
        
        if (!is_file($from)) {
            $code = 1;
        } elseif (!@rename($from, $to)) {
            @mkdir(dirname($to) , 0777, true);

            if (!@rename($from, $to)) {
                $code = 1;
            }
        }

        // Revert umask
        if (function_exists('umask')) {
            umask($oldUmask);
        }
        if (1 == $code) {
            throw new Exception('Unable to move file (' . $from . ') -> (' . $to . ')');
        }
    }

    protected function _delete($file) {
        // Delete
        $code = 0;
        
        if (is_file($file)) {
            
            if (!@unlink($file)) {
                @chmod($file, 0777);
                
                if (!@unlink($file)) {
                    $code = 1;
                }
            }
        }
        
        if (1 == $code) {
            throw new Exception('Unable to delete file: ' . $file);
        }
    }

    protected function _copy($from, $to) {

        // Change umask
        if (function_exists('umask')) {
            $oldUmask = umask();
            umask(0);
        }

        // Copy
        $code = 0;
        
        if (!is_file($from)) {
            $code = 1;
        } elseif (!@copy($from, $to)) {
            @mkdir(dirname($to) , 0777, true);
            @chmod(dirname($to) , 0777);
            
            if (!@copy($from, $to)) {
                $code = 1;
            }
        }

        // Revert umask
        if (function_exists('umask')) {
            umask($oldUmask);
        }
        if (1 == $code) {
            throw new Exception('Unable to copy file (' . $from . ') -> (' . $to . ')');
        }
    }

    protected function _write($file, $data) {

        // Change umask
        if (function_exists('umask')) {
            $oldUmask = umask();
            umask(0);
        }

        // Write
        $code = 0;
        
        if (!@file_put_contents($file, $data)) {
            
            if (is_file($file)) {
                @chmod($file, 0666);
            } elseif (is_dir(dirname($file))) {
                @chmod(dirname($file) , 0777);
            } else {
                @mkdir(dirname($file) , 0777, true);
            }

            if (!@file_put_contents($file, $data)) {
                $code = 1;
            }
        }

        // Revert umask
        if (function_exists('umask')) {
            umask($oldUmask);
        }
        
        if (1 == $code) {
            throw new Exception(sprintf('Unable to write to file: $s', $file));
        }
    }

    protected function _read($file) {
        if (!@file_get_contents($file)) {
            throw new Exception('Unable to read file: ' . $file);
        }
    }
    
    
    /* ================= Mapper Methods ====================*/
    public function createFile($file, $params = array())
    {
        // get limit
        $space_limit = (int) 0; //TODO: general_quota

        // fetch user
        if (!isset($params['user_id']) || empty($params['user_id'])) {
            $params['user_id'] = Yii::app()->user->id;
        }
        // member level quota
        if( null !== $params['user_id'] ) {
            $space_used = (int) Yii::app()->db->createCommand()
                ->select('SUM(size) AS space_used')
                ->from('storage_files')
                ->where("user_id=:id", array(':id'=>Yii::app()->user->id))
                ->queryColumn();

            $space_required = 1;
            if ($file instanceof CUploadedFile) {
                $space_required = $file->getSize();
            } elseif (is_array($file) && isset($file['size'])) {
                $space_required = $file['size'];
            } elseif (is_string($file)) {
                $space_required = filesize($file);
            }
   
            if ( $space_limit > 0 && $space_limit < ($space_used + $space_required) ) {
                throw new Exception("File creation failed. You may be over your " .
                    "upload limit. Try uploading a smaller file, or delete some files to " .
                    "free up space. ", self::SPACE_LIMIT_REACHED_CODE);
            }
        }
        
        $modelClass = $this->modelClass;
        
        $model = new $modelClass();
        $model->setAttributes($params);
        $model->store($file);
        
        return $model;
    }
    
    public function getModel() {
        return call_user_func(array($this->modelClass, 'model'));
    }

    public function getFile($id, $relationship = null)
    {
        $key = $id . '_' . ( $relationship ? $relationship : 'default' );
  
        if (!array_key_exists($key, $this->_files)) {
            $file = null;
            if ($relationship) {
                $file = $this->getModel()->findAllAttributes(array(
                    'parent_file_id' => $id,
                    'type' => $relationship,
                ));
            }

            if ( null === $file ) {
                $file = $this->getModel()->findByPk($id);;
            }
   
            $this->_files[$key] = $file;
        }
 
       return $this->_files[$key];
    }
 
    public function createSystemFile($file, $params = array())
    {
        $params = array_merge(array(
            'parent_id' => 0,
            'user_id' => null,
        ), $params);
        $params['parent_type'] = 'system';
        return $this->createFile($file, $params);
    }
 
    public function createTemporaryFile($file, $params = array())
    {
        $params = array_merge(array(
            'parent_id' => 0,
            'user_id' => null,
        ), $params);
        $params['parent_type'] = 'temporary';
        return $this->createFile($file, $params);
    }
 
    public function gc()
    {
        // Delete temporary files
        $files = $this->getModel()->findAll(array(
            'condition'=>'parent_type=:parent_type AND creation_date <= DATE_SUB(NOW(), INTERVAL 1 DAY)',
            'params'=>array(':parent_type'=>'temporary'),
        ));
        
        foreach($files as $file) {
            $file->remove();
        }
        
        return $this;
    }
}
