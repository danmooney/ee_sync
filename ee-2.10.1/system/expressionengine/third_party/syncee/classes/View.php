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

class Syncee_View
{
    public static function render($template_filename, array $vars = array())
    {
        extract($vars);

        ob_start();
        include SYNCEE_PATH_VIEWS . '/_shared/menu.php';
        $menu_html = ob_get_clean();

        return sprintf(
            '<div id="syncee">%s<div id="syncee-page">%s</div></div>',
            $menu_html,
            ee()->load->view(
                Syncee_Helper::convertCamelCaseToUnderscore($template_filename),
                array_merge(
                    $vars,
                    array(

                    )
                ),
                true
            )
        );
    }

    public static function addStylesheets()
    {
        $module_theme_path = self::_getThemePath();

        foreach (glob($module_theme_path . '/css/*') as $stylesheet_pathname) {
            $stylesheet_url = self::_getThemeUrl() . '/css/' . basename($stylesheet_pathname);
            ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $stylesheet_url . '">');
        }
    }

    public static function addScripts()
    {
        $module_theme_path = self::_getThemePath();

        foreach (glob($module_theme_path . '/js/*') as $script_pathname) {
            $script_url = self::_getThemeUrl() . '/js/' . basename($script_pathname);
            ee()->cp->add_to_foot('<script src="' . $script_url . '"></script>');
        }
    }

    private static function _getThemeUrl()
    {
        return rtrim(URL_THIRD_THEMES, '/') . '/' . strtolower(Syncee_Upd::MODULE_NAME);
    }

    private static function _getThemePath()
    {
        return rtrim(PATH_THIRD_THEMES, '/') . '/' . strtolower(Syncee_Upd::MODULE_NAME);
    }
}