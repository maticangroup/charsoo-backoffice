<?php
/**
 * Created by PhpStorm.
 * User: hossein
 * Date: 22/07/19
 * Time: 12:10
 */

namespace App;


class Params
{
    public static function get($param)
    {
        if ($param == 'PERMISSION_CACHE_FILE') {
            $public = DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
            $serverRoot = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;
            $cacheDir = 'cache' . DIRECTORY_SEPARATOR . 'Permissions' . DIRECTORY_SEPARATOR;
            $cacheFile = 'rolePermissions.txt';
            return str_replace($public, '', $serverRoot) . DIRECTORY_SEPARATOR . $cacheDir . $cacheFile;
        }
        if ($param == 'PERMISSION_CACHE_DIR') {
            $public = DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
            $serverRoot = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;
            $cacheDir = 'cache' . DIRECTORY_SEPARATOR . 'Permissions' . DIRECTORY_SEPARATOR;
            return str_replace($public, '', $serverRoot) . DIRECTORY_SEPARATOR . $cacheDir;
        }
    }

    public static function loginPageUrl()
    {
        return "/login";
    }

}