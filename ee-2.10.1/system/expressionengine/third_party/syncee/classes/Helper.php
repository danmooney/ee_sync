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

class Syncee_Helper
{
    public static function convertCamelCaseToUnderscore($camel_case_str)
    {
        preg_match_all('#([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)#', $camel_case_str, $matches);

        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match === strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    public static function convertUnderscoreToCamelCase($underscore_str)
    {
        $underscore_str_exploded             = explode('_', $underscore_str);
        $capitalized_underscore_str_exploded = array_map('ucfirst', $underscore_str_exploded);

        return lcfirst(implode('', $capitalized_underscore_str_exploded));
    }

    public static function createModuleCpUrl($method = '', array $additional_query_params = array())
    {
        $base = BASE;
        $path = str_replace('&amp;', '&', $base) . '&C=addons_modules&M=show_module_cp&module=' . strtolower(Syncee_Upd::MODULE_NAME);

        // remove all query params that are in request blacklist
        $additional_query_params = array_diff_key($additional_query_params, array_flip(Syncee_Form_Abstract::getRequestBlacklist()));

        if ($method) {
            $path .= '&method=' . $method;
        }

        $additional_query_params = array_filter($additional_query_params, function ($additional_query_param) {
            return $additional_query_param !== null;
        });

        if ($additional_query_params) {
            $path .= '&' . http_build_query($additional_query_params);
        }

        return $path;
    }

    public static function convertUTCDateToLocalizedHumanDatetime($utc_datetime)
    {
        return ee()->localize->human_time(strtotime($utc_datetime . ' UTC'));
    }

    public static function getClassNameFromPathname($pathname, $only_concrete_classes = true)
    {
        $contents = file_get_contents($pathname);

        // this helper primarily involves fetching conrete classes; return if class is abstract and we indeed only seek concrete class definitions
        if (stripos($contents, 'abstract class') !== false && $only_concrete_classes) {
            return false;
        }

        preg_match('#class ([a-z_]+)#i', $contents, $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $class_name = $matches[1];

        return $class_name;
    }

    public static function redirect($url, array $vars = array(), Syncee_Mcp_Abstract $mcp, $flash_message = true)
    {
        if (is_string($flash_message)) {
            Syncee_Helper_Flashdata::setFlashData($flash_message);
        } elseif (is_bool($flash_message) && $_SERVER['REQUEST_METHOD'] === 'POST' && !AJAX_REQUEST) {
            $called_method          = $mcp->getCalledMethod();
            $called_method_exploded = explode('_', Syncee_Helper::convertCamelCaseToUnderscore($called_method));
            $action                 = array_shift($called_method_exploded);

            array_pop($called_method_exploded); // pop off 'POST'

            $called_method_words    = ucwords(implode(' ', $called_method_exploded));

            switch ($action) {
                case 'new':
                    $verb = 'created';
                    break;
                case 'edit':
                    $verb = 'updated';
                    break;
                case 'delete':
                    $verb = 'deleted';
                    break;
                default:
                    $verb = 'saved';
                    break;
            }

            if (true === $flash_message) {
                $flash_message = $called_method_words . ' ' . $verb;
            } else {
                $flash_message = $called_method_words . ' ' . $verb;
            }

            Syncee_Helper_Flashdata::setFlashData($flash_message);
        }

        ee()->functions->redirect(static::createModuleCpUrl($url, $vars));
    }

    public static function prettyPrintJson($json)
    {
        if (defined('JSON_PRETTY_PRINT')) {
            $result = json_encode(json_decode($json, true), JSON_PRETTY_PRINT);
        } else {
            $result          = '';
            $level           = 0;
            $in_quotes       = false;
            $in_escape       = false;
            $ends_line_level = null;
            $json_length     = strlen($json);

            for ($i = 0; $i < $json_length; $i += 1) {
                $char = $json[$i];
                $new_line_level = null;
                $post = '';
                if ($ends_line_level !== null) {
                    $new_line_level = $ends_line_level;
                    $ends_line_level = null;
                }
                if ($in_escape) {
                    $in_escape = false;
                } else if ($char === '"') {
                    $in_quotes = !$in_quotes;
                } else if (!$in_quotes) {
                    switch ($char) {
                        case '}':
                        case ']':
                            $level--;
                            $ends_line_level = null;
                            $new_line_level = $level;
                            break;

                        case '{':
                        case '[':
                            $level++;
                        case ',':
                            $ends_line_level = $level;
                            break;

                        case ':':
                            $post = " ";
                            break;

                        case ' ':
                        case "\t":
                        case "\n":
                        case "\r":
                            $char = '';
                            $ends_line_level = $new_line_level;
                            $new_line_level = null;
                            break;
                    }
                } else if ($char === '\\') {
                    $in_escape = true;
                }

                if ($new_line_level !== null) {
                    $result .= "\n" . str_repeat("\t", $new_line_level);
                }

                $result .= $char . $post;
            }
        }

        return !trim($result) || in_array($result, array('null', 'false'))
            ? htmlspecialchars($json)
            : $result
        ;
    }
}