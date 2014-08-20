<?php

class YiiStorageServiceSchemeExtended extends YiiStorageServiceSchemeAbstract
{
    public function generate(array $params)
    {
        $this->validateParams($params);
    
        extract($params);
    
        $subdir1 = ( (int) $parent_id + 999999 - ( ( (int) $parent_id - 1 ) % 1000000) );
        $subdir2 = ( (int) $parent_id + 999    - ( ( (int) $parent_id - 1 ) % 1000   ) );
    
        return strtolower($parent_type) . '/'
          . $subdir1 . '/'
          . $subdir2 . '/'
          . $parent_id . '/'
          . $file_id . '.'
          . strtolower($extension);
    }
}