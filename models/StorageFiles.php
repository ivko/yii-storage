<?php

/**
 * This is the model class for table "storage_files".
 *
 * The followings are the available columns in table 'storage_files':
 * @property string $id
 * @property string $parent_file_id
 * @property string $type
 * @property string $parent_type
 * @property string $parent_id
 * @property string $user_id
 * @property string $creation_date
 * @property string $modified_date
 * @property string $service_id
 * @property string $storage_path
 * @property string $extension
 * @property string $name
 * @property string $mime_major
 * @property string $mime_minor
 * @property string $size
 * @property string $hash
 */
class StorageFiles extends ActiveRecord
{
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'storage_files';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('storage_path, creation_date, modified_date, extension, mime_major, mime_minor, size, hash', 'required'),
            array('parent_file_id, parent_id, user_id, service_id', 'length', 'max'=>10),
            array('type', 'length', 'max'=>16),
            array('parent_type', 'length', 'max'=>32),
            array('storage_path, name', 'length', 'max'=>255),
            array('extension', 'length', 'max'=>8),
            array('mime_major, mime_minor, hash', 'length', 'max'=>64),
            array('size', 'length', 'max'=>20),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, parent_file_id, type, parent_type, parent_id, user_id, creation_date, modified_date, service_id, storage_path, extension, name, mime_major, mime_minor, size, hash', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'parent_file_id' => 'Parent File',
            'type' => 'Type',
            'parent_type' => 'Parent Type',
            'parent_id' => 'Parent',
            'user_id' => 'User',
            'creation_date' => 'Creation Date',
            'modified_date' => 'Modified Date',
            'service_id' => 'Service',
            'storage_path' => 'Storage Path',
            'extension' => 'Extension',
            'name' => 'Name',
            'mime_major' => 'Mime Major',
            'mime_minor' => 'Mime Minor',
            'size' => 'Size',
            'hash' => 'Hash',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id,true);
        $criteria->compare('parent_file_id',$this->parent_file_id,true);
        $criteria->compare('type',$this->type,true);
        $criteria->compare('parent_type',$this->parent_type,true);
        $criteria->compare('parent_id',$this->parent_id,true);
        $criteria->compare('user_id',$this->user_id,true);
        $criteria->compare('creation_date',$this->creation_date,true);
        $criteria->compare('modified_date',$this->modified_date,true);
        $criteria->compare('service_id',$this->service_id,true);
        $criteria->compare('storage_path',$this->storage_path,true);
        $criteria->compare('extension',$this->extension,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('mime_major',$this->mime_major,true);
        $criteria->compare('mime_minor',$this->mime_minor,true);
        $criteria->compare('size',$this->size,true);
        $criteria->compare('hash',$this->hash,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return StorageFiles the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    protected function getDeafaultValues() {
        return array(
            'storage_path' => 'temp',
            'creation_date' => date('Y-m-d H:i:s'),
            'modified_date' => date('Y-m-d H:i:s')
        );
    }
    
    public function toArray($full = true, $withRelated = true)
    {
        $data = parent::toArray(false, false);
        $data['url'] = $this->getHref();
        if ($this->mime_major == 'image') {
            $data['thumbnail_url'] = $this->getHref();
        }
        return $data;
    }
    
    /*==============================================================*/
    public function getId() {
        return isset($this->id) ? $this->id : false;
    }

    public function getLocalPath()
    {
        return $this->getStorageService()->getLocalPath($this);
    }
   
    public function getHref()
    {
        return $this->getStorageService()->map($this);
    }

    public function getParent($recurseType = null)
    {
        if( $this->getParentType() == 'temporary' || $this->getParentType() == 'system' ) {
            return null;
        } else {
            return parent::getParent($recurseType);
        }
    }

    // Storage stuff
    public function getStorageService($type = 1)
    {
        return Yii::app()->storageService;
    }
  
    public function getChildren()
    {
        return self::model()->findAllByAttributes(array(
            'parent_file_id' => $this->getId()
        ));
    }
   
   // Simple operations
    public function bridge(StorageFiles $file, $type, $isChild = false)
    {
        $child  = ( $isChild ? $this : $file );
        $parent = ( $isChild ? $file : $this );
        $child->parent_file_id = $parent->getId();
        $child->type = $type;
        $child->update();
        return $this;
    }

    public function map()
    {
        $uri = $this->getStorageService()->map($this);
        $uri .= '?c=' . substr($this->getHash(), 0, 4);
        return $uri;
    }

    public function store($file)
    {
        $service   = $this->getStorageService();

        $meta      = $service->fileInfo($file);

        $meta['service_id'] = $service->getIdentity();

        if (empty($this->user_id) && $this->parent_type != 'temporary' && $this->parent_type != 'system') {
            $this->user_id = Yii::app()->user->id;
        }
        
        $this->setAttributes($meta);
        
        // Have to initialize now if creation
        if ($this->getId() === false) {
            if (!$this->save()) {
                return false;
                // TODO: return error message
            }
            $this->refresh();
        }
        
        // Store file to service
        $path = $service->store($this, $meta['tmp_name']);

        // If a file existed before and not same name, try to remove the old one
        $storage_path = $this->storage_path;

        if (!empty($storage_path) && $storage_path != 'temp' && $storage_path != $path) {
            $service->removeFile($storage_path);
        }
  
        // We still have to update the path even if we just created it
        $this->storage_path = $path;

        $this->update();

        return $this;
    }
    
    public function write($data, $meta)
    {
        $service   = $this->getStorageService();
        $isCreate  = $this->getId() === false;

        $meta['hash'] = md5($data);
        $meta['size'] = strlen($data);

        $this->reset($meta, false);

        $this->service_id = $service->getIdentity();
        if (empty($this->_data['user_id']) && $this->isTemporary() && $this->isSystem()) {
            $this->user_id = Yii::app()->user->id;
        }
  
        // Have to initialize now if creation
        if( $isCreate ) {
            $this->save();
        }
      
        // Write data to service
        $path = $service->write($this, $data);
  
        // If a file existed before and not same name, try to remove the old one
        $storage_path = $this->getStoragePath();
        if (!empty($storage_path) && $storage_path != 'temp' && $storage_path != $path) {
            $service->removeFile($storage_path);
        }

        // We still have to update the path even if we just created it
        $this->setStoragePath($path);
        $this->update();
  
        return $this;
    }

    public function read()
    {
        return $this->getStorageService()->read($this);
    }

    public function remove($children = false)
    {
        if ($children) {
            foreach($this->getChildren() as $child) {
                $child->remove($children);
            }
        }
        $this->getStorageService()->remove($this);
        return $this->delete();
    }

    public function isTemporary()
    {
        return  $this->parent_type === 'temporary';
    }
    
    public function isSystem()
    {
        return  $this->parent_type === 'system';
    }

    public function temporary()
    {
        return $this->getStorageService()->temporary($this);
    }

   // Complex
    public function copy($params = array())
    {
        $storage = $this->getStorageService(); // TODO: pass service type for multiple services

        // @todo store this in main model?
        $params = array_merge($this->toArray(), $params);
        $params['service_id'] = $storage->getIdentity();
        $params['storage_path'] = 'temp';
        unset($params['id']);

        $copy = new self;
        $copy->setAttributes($params);
        $copy->save();

        // Read into temp file and store
        $tmp_file = $storage->temporary($this);
        $path = $storage->store($copy, $tmp_file);
        
        // Update
        // @todo make sure file is removed if this fails
        $copy->storage_path = $path;
        $copy->update();

        // Remove temp file
        @unlink($tmp_file);

        return $copy;
    }
    public function updatePath()
    {
        $service = $this->getStorageService();
        $oldPath = $this->storage_path;
        $newPath = $service->getScheme()->generate($this->toArray());
    
    
        dump($oldPath);
        // No update required
        if ( $oldPath == $newPath ) {
            return $this;
        }

        // @todo maybe update this to move the file internally
        $tmpFile = $this->temporary();

        // Store file to service
        $path = $service->store($this, $tmpFile);

        // Update the path and remove the old file if necessary
        if( $oldPath != $path ) {
            $this->storage_path = $path;
            $this->update();
            $service->removeFile($oldPath);
        }
        return $this;
    }
  
	public function insert()
	{
        $this->creation_date = date('Y-m-d H:i:s');
        return parent::insert();
	}
   
    public function update()
	{
        $this->modified_date = date('Y-m-d H:i:s');
        return parent::update();
	}

    public function delete()
	{
        return parent::delete();
	}
}