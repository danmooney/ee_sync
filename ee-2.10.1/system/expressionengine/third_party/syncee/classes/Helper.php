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
        $base = ee()->config->item('cp_url', false) . '?D=cp';
        $path = str_replace('&amp;', '&', $base) . '&C=addons_modules&M=show_module_cp&module=' . strtolower(Syncee_Upd::MODULE_NAME);

        if ($method) {
            $path .= '&method=' . $method;
        }

        if ($additional_query_params) {
            $path .= '&' . http_build_query($additional_query_params);
        }

        // add session fingerprint
        $path .= '&S=' . $_REQUEST['S'];

        return $path;
    }

    public static function convertUTCDateToLocalizedHumanDatetime($utc_datetime)
    {
        return ee()->localize->human_time(strtotime($utc_datetime . ' UTC'));
    }
}