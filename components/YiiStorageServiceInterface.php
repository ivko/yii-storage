<?php

interface YiiStorageServiceInterface
{
    /**
    * Returns a string that identifies the storage type
    *
    * @return string
    */
    public function getIdentity();
   
    /**
    * Returns a string that identifies the storage type
    *
    * @return string
    */
    public function getType();
 
    /**
    * Returns a url that allows for external access to the file. May point to some
    * adapter which then retrieves the file and outputs it, if desirable
    *
    * @param YiiStorageModelDbRowFile The file for operation
    * @return string
    */
    public function map(StorageFilesAbstract $model);
 
    /**
    * Stores a local file in the storage service
    *
    * @param ZendFormElementFile|array|string $file Temporary local file to store
    * @param array $params Contains iden
    * @return string Storage type specific path (internal use only)
    */
    public function store(StorageFilesAbstract $model, $file);
 
    /**
    * Returns the content of the file
    *
    * @param YiiStorageModelDbRowFile $model The file for operation
    * @param array $params
    */
    public function read(StorageFilesAbstract $model);
 
    /**
    * Creates a new file from data rather than an existing file
    *
    * @param YiiStorageModelDbRowFile $model The file for operation
    * @param string $data
    */
    public function write(StorageFilesAbstract $model, $data);
 
    /**
    * Removes the file
    *
    * @param YiiStorageModelDbRowFile $model The file for operation
    */
    public function remove(StorageFilesAbstract $model);
 
    /**
    * Removes a file
    *
    * @param string $path The file for operation
    */
    public function removeFile($path);
 
    /**
    * Creates a local temporary local copy of the file
    *
    * @param YiiStorageModelDbRowFile $model The file for operation
    */
    public function temporary(StorageFilesAbstract $model);
 
    /**
    * Get the naming scheme object
    *
    * @return YiiStorageServiceSchemeInterface
    */
    public function getScheme();
 
    /**
     * Sets the naming scheme object
     *
     * @param YiiStorageServiceSchemeInterface $scheme
     * @return YiiStorageServiceInterface
     */
    public function setScheme(YiiStorageServiceSchemeInterface $scheme);
}
