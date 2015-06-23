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

class Syncee_Mcp_Site_Local extends Syncee_Mcp_Abstract
{
    public function viewLocalSiteList()
    {
        $ee_sites           = ee()->db->get('sites')->result_object();
        $syncee_local_sites = Syncee_Site::getLocalSiteCollection();

        return Syncee_View::render(__FUNCTION__, array(
            'ee_sites' => $ee_sites,
        ), $this);
    }

    public function viewLocalSite()
    {
        return Syncee_View::render(__FUNCTION__, array(), $this);
    }
}