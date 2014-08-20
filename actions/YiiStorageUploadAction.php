<?php

class YiiStorageUploadAction extends CAction {

    public $formClass = 'xupload.models.XUploadForm';

    public $fileAttribute = 'file';

    public $mimeTypeAttribute = 'mime_type';

    public $sizeAttribute = 'size';

    public $displayNameAttribute = 'name';

    public $fileNameAttribute = 'filename';

    public $secureFileNames = true;
    
    public $formModel = null;
    
    private $_formModel;

    public function init( ) {

        if( !isset($this->_formModel)) {
            $this->formModel = Yii::createComponent(array('class'=>$this->formClass));
        }

        if($this->secureFileNames) {
            $this->formModel->secureFileNames = true;
        }
    }

    public function run( ) {
        // Debug
        if (YII_DEBUG) {
            foreach (Yii::app()->log->routes as $route) {
                $route->enabled=false;
            }
        }
        
        $this->sendHeaders();

        $this->handleDeleting() or $this->handleUploading();
    }
    
    protected function sendHeaders()
    {
        header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
    }
    
    /**
     * Removes temporary file from its directory and from the session
     *
     * @return bool Whether deleting was meant by request
     */
    protected function handleDeleting()
    {
        $method = Yii::app()->request->getParam('_method', null);
        $fileId = Yii::app()->request->getParam('file_id', null);
        if ($method == "delete" && $fileId > 0) {
            $file = Yii::app()->storageService->getFile($fileId);
            echo json_encode($file->remove());
            return true;
        }
        return false;
    }

    /**
     * Uploads file to temporary directory
     *
     * @throws CHttpException
     */
    protected function handleUploading()
    {
        $this->init();

        $model = $this->formModel;

        $attribute = Yii::app()->request->getParam('attribute', $this->fileAttribute);
        $modelName = Yii::app()->request->getParam('model', null);
        $uploadedFileAttribute = $modelName !== null ? $modelName .'['. $attribute . ']' : $attribute;
        $uploadedFile = CUploadedFile::getInstanceByName($uploadedFileAttribute);

        if ($uploadedFile !== null) {

            $model->{$this->fileAttribute} = $uploadedFile;
            $model->{$this->mimeTypeAttribute} = $uploadedFile->getType();
            $model->{$this->sizeAttribute} = $uploadedFile->getSize();
            $model->{$this->fileNameAttribute} = $uploadedFile->getName();

            if ($model->validate()) {

                $file = Yii::app()->storageService->createTemporaryFile($uploadedFile);

                if ($file instanceof StorageFilesAbstract) {
                    $data = $file->toArray();
                    $data['delete_type'] = "POST";
                    $data['delete_url'] = $this->getController()->createUrl($this->getId(), array(
                        "_method" => "delete",
                        "file_id" => $file->getId(),
                    ));
                    echo json_encode(array($data));
                } else {
                    echo json_encode(array(array("error" => $file,)));
                    Yii::log("StorageUploadAction: " . $file, CLogger::LEVEL_ERROR, "yii-storage.actions.StorageUploadAction");
                }
            } else {
                echo json_encode(array(array("error" => $model->getErrors($this->fileAttribute),)));
                Yii::log("StorageUploadAction: " . CVarDumper::dumpAsString($model->getErrors()), CLogger::LEVEL_ERROR, "yii-storage.actions.StorageUploadAction");
            }
        } else {
            throw new CHttpException(500, "Could not upload file");
        }
    }
}
