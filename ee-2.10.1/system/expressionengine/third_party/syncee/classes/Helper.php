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

    public static function createModuleCpUrl($method = '', array $additional_query_params = array())
    {
        $base = BASE;
        $path = str_replace('&amp;', '&', $base) . '&C=addons_modules&M=show_module_cp&module=' . strtolower(Syncee_Upd::MODULE_NAME);

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
}