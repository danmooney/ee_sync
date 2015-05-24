<?php

// perform BASEPATH check and add simple module autoloader

if (!defined('BASEPATH')) {
    header('HTTP 1.1 403 Forbidden', true, 403);
    exit('Direct Script Access Not Allowed');
}

$module_autoloader = function ($class_name) {
    $module_name = basename(dirname(__FILE__));
    $class_name  = strtolower($class_name);

    $class_name_begins_with_module_name = strpos($class_name, $module_name) === 0;
    $is_test_class                      = strpos($class_name, 'test_') === 0;

    if (!$class_name_begins_with_module_name && !$is_test_class) {
        return;
    }

    if ($class_name_begins_with_module_name) {
        switch ($class_name) {
            case $module_name . '':
                require_once dirname(__FILE__) . '/mod.' . strtolower($module_name) . '.php';
                break;
            case $module_name . '_upd':
                require_once dirname(__FILE__) . '/upd.' . strtolower($module_name) . '.php';
                break;
            case $module_name . '_mcp':
                require_once dirname(__FILE__) . '/mcp.' . strtolower($module_name) . '.php';
                break;
        }
    }

    if ($is_test_class) {

    }
};

spl_autoload_register($module_autoloader);