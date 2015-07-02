<?php

if (!defined('SYNCEE_PATH')) {
    $current_dir = dirname(__FILE__);
    $i           = 1;

    while (($ancestor_realpath = realpath($current_dir . str_repeat('/..', $i++)) . '/_init.php') && !is_readable($ancestor_realpath)) {
        $is_at_root = substr($ancestor_realpath, -1) === PATH_SEPARATOR;
        if ($is_at_root) {
            break;
        }
    }

    if (!is_readable($ancestor_realpath)) {
        show_error('Could not find _init.php for module');
    }

    require_once $ancestor_realpath;
}

class Syncee_Helper_Flashdata
{
    private static $_flash_data_cookie_key = 'syncee_flash_data';

    private static $_flash_message;

    private static $_flash_message_type;

    public static function setFlashData($message, $message_type = 'success')
    {
        $data = serialize(array(
            'message'      => $message,
            'message_type' => $message_type
        ));

        setcookie(static::$_flash_data_cookie_key, $data, time() + 3600);
        $_COOKIE[static::$_flash_data_cookie_key] = $data;
    }

    public static function getFlashMessage($unset_cookie = true)
    {
        static::_wakeupFlashData($unset_cookie);
        return static::$_flash_message;
    }

    public static function getFlashMessageType($unset_cookie = true)
    {
        static::_wakeupFlashData($unset_cookie);
        return static::$_flash_message_type;
    }

    public static function deleteFlashMessage()
    {
        if (!isset($_COOKIE[static::$_flash_data_cookie_key])) {
            return;
        }

        unset($_COOKIE[static::$_flash_data_cookie_key]);
        setcookie(static::$_flash_data_cookie_key, '', time() - 3600);
    }

    private static function _wakeupFlashData($unset_cookie = true)
    {
        if (!isset($_COOKIE[static::$_flash_data_cookie_key])) {
            return;
        }

        list($message, $message_type) = array_values(unserialize($_COOKIE[static::$_flash_data_cookie_key]));

        static::$_flash_message      = $message;
        static::$_flash_message_type = $message_type;

        if ($unset_cookie) {
            static::deleteFlashMessage();
        }
    }
}