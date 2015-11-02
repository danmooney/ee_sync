<?php

// perform BASEPATH check and add simple module autoloader

if (!defined('BASEPATH')) {
    header('HTTP 1.1 403 Forbidden', true, 403);
    exit('Direct Script Access Not Allowed');
}

if (!extension_loaded('json')) {
    header('HTTP 1.1 500 Internal Server Error', true, 500);
    die('JSON extension required for this module.');
}

if (!extension_loaded('curl')) {
    header('HTTP 1.1 500 Internal Server Error', true, 500);
    die('Curl extension required for this module.');
}

// JSON_PRETTY_PRINT is PHP 5.4+
if (!defined('JSON_PRETTY_PRINT')) {
    define('JSON_PRETTY_PRINT', 0);
}

$minimum_php_version_major_and_minor_only = '5.3';
$php_version_major_and_minor_only         = preg_replace('#\.\d+$#', '', PHP_VERSION);


if (version_compare($php_version_major_and_minor_only, $minimum_php_version_major_and_minor_only) < 0) {
    header('HTTP 1.1 500 Internal Server Error', true, 500);
    die("PHP version must be at least $minimum_php_version_major_and_minor_only;  the version installed on the server is " . $php_version_major_and_minor_only . '.  Please upgrade in order to use this module.');
}

// This addon just isn't gonna work with all that eval'd code EE likes to do if short_open_tag is off.
// '<?=' is the only short tag in use in this addon so PHP 5.4+ will not have any issue as long as we're not eval'ing the code.
// The reason it can't be eval'd is because magic constant __FILE__ will simply not produce the proper value in an eval'd context.
$php_version_is_53_or_less                         = version_compare($php_version_major_and_minor_only, '5.3') <= 0;
$php_unable_to_parse_short_open_tag                = $php_version_is_53_or_less && !ini_get('short_open_tag');
$ee_will_eval_templates                            = !ini_get('short_open_tag') && config_item('rewrite_short_tags');
$ee_will_literally_output_unparsed_short_open_tags = $php_unable_to_parse_short_open_tag && !config_item('rewrite_short_tags');

if ($php_unable_to_parse_short_open_tag) {
    header('HTTP 1.1 500 Internal Server Error', true, 500);
    die('short_open_tag PHP setting must be turned on, or you must upgrade to PHP 5.4 or later.');
}

if ($ee_will_literally_output_unparsed_short_open_tags) {
    header('HTTP 1.1 500 Internal Server Error', true, 500);
    die('rewrite_short_tags EE config item must be turned on in order for this addon to work on your installation.');
}

if ($ee_will_eval_templates) {
    header('HTTP 1.1 500 Internal Server Error', true, 500);
    die('short_open_tag PHP setting must be turned on, or you must turn off the rewrite_short_tags EE config item.');
}

// TODO - start tracking memory usage in tests/for the entire module in general while building
defined('SYNCEE_VERSION')         or define('SYNCEE_VERSION',        '0.1.0');
defined('SYNCEE_VERSION_FREE')    or define('SYNCEE_VERSION_FREE',   substr_count(SYNCEE_VERSION, 'free') === 1);
defined('SYNCEE_EE_VERSION')      or define('SYNCEE_EE_VERSION',     defined('APP_VER') ? APP_VER : null);
defined('SYNCEE_URL')             or define('SYNCEE_URL',            'http://www.sync-ee.com');
defined('SYNCEE_PATH')            or define('SYNCEE_PATH',           dirname(__FILE__));
defined('SYNCEE_PATH_TESTS')      or define('SYNCEE_PATH_TESTS',     SYNCEE_PATH . '/tests');
defined('SYNCEE_TEST_MODE')       or define('SYNCEE_TEST_MODE',      isset($_SERVER['SYNCEE_TEST_MODE']) && $_SERVER['SYNCEE_TEST_MODE']);
defined('SYNCEE_UNIT_TEST_MODE')  or define('SYNCEE_UNIT_TEST_MODE', SYNCEE_TEST_MODE && isset($_GET['SYNCEE_UNIT_TEST_MODE']));
defined('SYNCEE_PATH_VIEWS')      or define('SYNCEE_PATH_VIEWS',     SYNCEE_PATH . '/views');

if (SYNCEE_TEST_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);
}

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
                $pathname_to_class_file = SYNCEE_PATH . '/mod.' . strtolower($module_name) . '.php';
                break;
            case $module_name . '_upd':
                $pathname_to_class_file = SYNCEE_PATH . '/upd.' . strtolower($module_name) . '.php';
                break;
            case $module_name . '_mcp':
                $pathname_to_class_file = SYNCEE_PATH . '/mcp.' . strtolower($module_name) . '.php';
                break;
            default:
                // remove '_free' from module name if free version
                $module_name                          = str_replace('_free', '', $module_name);
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

                $relative_path_to_class_file = implode('/', $relative_path_to_class_file_exploded);
                $pathname_to_class_file      = SYNCEE_PATH . '/classes/' . $relative_path_to_class_file . '.php';

                break;
        }

        if (is_readable($pathname_to_class_file)) {
            require_once $pathname_to_class_file;
        }
    }

    if ($is_test_class) {

    }
};

spl_autoload_register($module_autoloader);

if (isset($idiom)) {
    Syncee_Lang::setLanguage($idiom);
} elseif (isset($GLOBALS['LANG']) && is_object($GLOBALS['LANG']) && isset($GLOBALS['LANG']->user_lang)) {
    Syncee_Lang::setLanguage($GLOBALS['LANG']->user_lang);
}