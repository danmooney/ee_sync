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
    public static function render($template_filename, array $vars = array(), Syncee_Mcp_Abstract $mcp)
    {
        static::setPageTitle(static::_getPageTitleByMcpAndVars($mcp, $vars));

        extract($vars);

        $flash_message      = Syncee_Helper_Flashdata::getFlashMessage();
        $flash_message_type = Syncee_Helper_Flashdata::getFlashMessageType();

        // render shared files
        ob_start();
        include SYNCEE_PATH_VIEWS . '/_shared/menu.php';
        include SYNCEE_PATH_VIEWS . '/_shared/version.php';
        $menu_html = ob_get_clean();

        ob_start();
        include SYNCEE_PATH_VIEWS . '/_shared/flash_message.php';
        $flash_message_html = ob_get_clean();

        $vars = array_merge(
            $vars,
            array(
                'mcp'        => $mcp,
                'theme_path' => static::_getThemePath(),
                'theme_url'  => static::_getThemeUrl(),
            )
        );

        $has_paginator = isset($vars['paginator']) && $vars['paginator'] instanceof Syncee_Paginator;

        $paginator_html = $has_paginator
            ? ee()->load->view(
                '_shared/pagination.php',
                $vars,
                true
            ) : ''
        ;

        $image_cache_html = ee()->load->view('_shared/image_cache.php', $vars, true);

        return sprintf(
            '<div id="syncee">%s<div id="syncee-page">%s %s<div style="clear:both;"></div>%s%s</div></div>',
            $menu_html,
            $flash_message_html,
            ee()->load->view(
                Syncee_Helper::convertCamelCaseToUnderscore($template_filename),
                $vars,
                true
            ),
            $paginator_html,
            $image_cache_html
        );
    }

    public static function setPageTitle($title)
    {
        $ee = ee();

        if (isset($ee->cp) && method_exists($ee->cp, 'set_variable')) {
            $ee->cp->set_variable('cp_heading',    $title);
            $ee->cp->set_variable('cp_page_title', strip_tags($title . ' | ' . Syncee_Upd::MODULE_NAME));
        } else {
            ee()->view->cp_heading    = $title;
            ee()->view->cp_page_title = strip_tags($title . ' | ' . Syncee_Upd::MODULE_NAME);
        }
    }

    public static function addStylesheets()
    {
        $module_theme_path = static::_getThemePath();

        foreach (glob($module_theme_path . '/css/*') as $stylesheet_pathname) {
            if (pathinfo($stylesheet_pathname, PATHINFO_EXTENSION) !== 'css') {
                continue;
            }

            $stylesheet_url = static::_getThemeUrl() . '/css/' . basename($stylesheet_pathname);
            ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $stylesheet_url . '">');
        }
    }

    public static function addScripts()
    {
        $module_theme_path = static::_getThemePath();

        foreach (glob($module_theme_path . '/js/*') as $script_pathname) {
            if (is_dir($script_pathname)) {
                continue;
            }

            $script_url = static::_getThemeUrl() . '/js/' . basename($script_pathname);
            ee()->cp->add_to_foot('<script src="' . $script_url . '"></script>');
        }
    }

    private static function _getPageTitleByMcpAndVars(Syncee_Mcp_Abstract $mcp, array $vars)
    {
        $view_method          = $mcp->getCalledMethod();

        if (!$view_method) {
            return false;
        }

        $view_method_exploded = explode('_', Syncee_Helper::convertCamelCaseToUnderscore($view_method));

        if ('view' === $view_method_exploded[0]) {
            array_shift($view_method_exploded);
        }

        if ('edit' === $view_method_exploded[0]) {
            $view_method_exploded[0] = 'update';
        }

        if ('list' === $view_method_exploded[count($view_method_exploded) - 1]) {
            array_pop($view_method_exploded);
            $view_method_exploded[count($view_method_exploded) - 1] .= 's';
        } else {
            foreach ($vars as $key => $val) {
                if ($val instanceof Syncee_ActiveRecord_Abstract && strlen($val->title)) {
                    $subject = $val->title;
                    break;
                }
            }
        }

        $page_title = ucwords(implode(' ', $view_method_exploded));

        if (isset($subject)) {
            $page_title .= sprintf(': <strong>%s</strong>', $subject);
        }

        return $page_title;
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