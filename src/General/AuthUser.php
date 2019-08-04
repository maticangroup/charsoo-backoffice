<?php
/**
 * Created by PhpStorm.
 * User: hossein
 * Date: 22/07/19
 * Time: 11:53
 */

namespace App\General;


use App\FormModels\Authentication\UserModel;
use App\FormModels\ModelSerializer;
use App\Params;
use Matican\Core\Entities\Authentication;
use Matican\Core\Servers;
use Symfony\Component\Filesystem\Filesystem;
use Matican\Core\Transaction\Request as Req;


class AuthUser
{
    public static function login($userModel)
    {
        @session_start();
        $_SESSION['user'] = $userModel;
        return true;
    }

    /**
     * @return UserModel | mixed
     */
    public static function current_user()
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return false;
        }
    }

    /**
     * @param $action string
     * @return bool
     */
    public static function if_is_allowed($action)
    {

        return true;
        /**
         * @var $currentUser UserModel
         */
        $currentUser = self::current_user();
//        if (!$currentUser) {
//            $loginPageUrl = Params::loginPageUrl();
//            $location = "Location: http://" . $_SERVER['HTTP_HOST'] . $loginPageUrl;
////            dd($location);
//            header($location);
//            die();
//        }
        $permissions = json_decode(self::getPermissions(), true);
        $userRolePermission = $permissions[$currentUser->getRoleId()];
        if (in_array($action, $userRolePermission)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getPermissions()
    {
        return file_get_contents(Params::get('PERMISSION_CACHE_FILE'));
    }

    public static function cachePermissions($permissionsJson)
    {
        $fileSystem = new Filesystem();
        $cacheFilePath = Params::get('PERMISSION_CACHE_FILE');
        $cacheFileDir = Params::get('PERMISSION_CACHE_DIR');

        if (!$fileSystem->exists($cacheFilePath)) {
            $fileSystem->mkdir($cacheFileDir);
            $fileSystem->touch($cacheFilePath);
        } else {
            $fileSystem->remove($cacheFilePath);
        }
        $fileSystem->mkdir($cacheFileDir);
        $fileSystem->touch($cacheFilePath);
        $cacheContent = json_encode($permissionsJson);
        $fileSystem->appendToFile($cacheFilePath, $cacheContent);
        return true;

    }

    public static function logout()
    {
        @session_start();
        $_SESSION['user'] = null;
        return true;
    }

    public static function check_if_user_is_logged_in()
    {
        @session_start();
        $currentUser = AuthUser::current_user();
        if ($_SERVER) {
            if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != Params::loginPageUrl()) {
//            dd('Location: http://' . Params::get('APPLICATION_DOMAIN') . Params::loginPageUrl());
                if (!$currentUser) {
//                die("sss");
//                dd(Params::get('APPLICATION_DOMAIN') . Params::loginPageUrl());
//                die;
                    header('Location: http://' . Params::get('APPLICATION_DOMAIN') . Params::loginPageUrl());
                    die;
                }
            }
        }
    }

    public static function purge_role_permissions()
    {
        $rolePermissionRequest = new Req(Servers::Authentication, Authentication::Role, 'get_roles_permissions');
        $response = $rolePermissionRequest->send();
        AuthUser::cachePermissions($response->getContent());
    }
}