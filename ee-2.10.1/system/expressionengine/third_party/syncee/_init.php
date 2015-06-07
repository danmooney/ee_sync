<?php

// perform BASEPATH check and add simple module autoloader

if (!defined('BASEPATH')) {
    header('HTTP 1.1 403 Forbidden', true, 403);
    exit('Direct Script Access Not Allowed');
}

defined('SYNCEE_PATH')       or define('SYNCEE_PATH',       dirname(__FILE__));
defined('SYNCEE_PATH_TESTS') or define('SYNCEE_PATH_TESTS', SYNCEE_PATH . '/tests');
defined('SYNCEE_TEST_MODE')  or define('SYNCEE_TEST_MODE',  false);

require_once SYNCEE_PATH . '/vendor/autoload.php';

$module_autoloader = function ($class_name) {
    $module_name          = basename(dirname(__FILE__));
    $lowercase_class_name = strtolower($class_name);

    $class_name_begins_with_module_name = strpos($lowercase_class_name, $module_name) === 0;
    $is_test_class                      = strpos($lowercase_class_name, 'test_') === 0;

    if (!$class_name_begins_with_module_name && !$is_test_class) {
        return;
    }

    if ($class_name_begins_with_module_name) {
        switch ($lowercase_class_name) {
            case $module_name . '':
                require_once SYNCEE_PATH . '/mod.' . strtolower($module_name) . '.php';
                break;
            case $module_name . '_upd':
                require_once SYNCEE_PATH . '/upd.' . strtolower($module_name) . '.php';
                break;
            case $module_name . '_mcp':
                require_once SYNCEE_PATH . '/mcp.' . strtolower($module_name) . '.php';
                break;
            default:
                $class_name_sans_module_name          = preg_replace("#^{$module_name}_#i", '', $class_name);
                $relative_path_to_class_file_exploded = explode('_', $class_name_sans_module_name);

                // need to support case-sensitive filesystems by naming relative paths appropriately
                array_walk($relative_path_to_class_file_exploded, function ($val) {

                    $first_letter_is_lowercase = strtolower(substr($val, 0, 1)) === substr($val, 0, 1);

                    if ($first_letter_is_lowercase) {
                        return ucfirst(strtolower($val));
                    }

                    return $val;
                });

                $relative_path_to_class_file          = implode('/', $relative_path_to_class_file_exploded);
                require_once SYNCEE_PATH . '/classes/' . $relative_path_to_class_file . '.php';
                break;
        }
    }

    if ($is_test_class) {

    }
};

spl_autoload_register($module_autoloader);