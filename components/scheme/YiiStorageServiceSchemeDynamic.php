<?php

class YiiStorageServiceSchemeDynamic extends YiiStorageServiceSchemeAbstract
{
    public function generate(array $params)
    {
        $this->validateParams($params);

        extract($params);

        $path = $parent_type . '/';
        $base = 255;
        $tmp = $id;

        // Generate subdirs while id > $base
        do {
            $mod = ( $tmp % $base );
            $tmp -= $mod;
            $tmp /= $base;
            $path .= sprintf("%02x", $mod) . '/';
        } while( $tmp > 0 );

        $path .= sprintf("%04x", $id)
          . '_' . substr($hash, 4, 4)
          . '.' . $extension;

        return $path;
    }
}