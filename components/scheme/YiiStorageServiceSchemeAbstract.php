<?php

class YiiStorageServiceSchemeAbstract implements YiiStorageServiceSchemeInterface
{
    public function generate(array $params)
    {
        throw new Exception('Unimplemented Method "generate"');
    }
    
    public function validateParams(array $params)
    {
        if ( empty($params['parent_type']) ) {
            throw new Exception('Unspecified resource parent type');
        } else if ( empty($params['id']) || !is_numeric($params['id']) ) {
            throw new Exception('Unspecified resource identifier');
        } else if ( empty($params['extension']) ) {
            throw new Exception('Unspecified resource extension');
        }
        return true;
    }
}