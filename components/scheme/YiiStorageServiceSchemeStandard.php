<?php

class YiiStorageServiceSchemeStandard extends YiiStorageServiceSchemeAbstract
{
    public function generate(array $params)
    {
        $this->validateParams($params);
    
        extract($params);
    
        $subdir = ( (int) $parent_id + 999 - ( ( (int) $parent_id - 1 ) % 1000) );
    
        return strtolower($parent_type) . '/'
            . $subdir . '/'
            . $parent_id . '/'
            . $file_id . '.'
            . strtolower($extension);
    }
}
