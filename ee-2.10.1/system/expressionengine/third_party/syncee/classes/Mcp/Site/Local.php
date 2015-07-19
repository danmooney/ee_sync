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
        $paginator          = new Syncee_Paginator_Site_Local($_GET, $this);
        $syncee_local_sites = Syncee_Site::getLocalSiteCollection($paginator);

        return Syncee_View::render(__FUNCTION__, array(
            'paginator'          => $paginator,
            'syncee_local_sites' => $syncee_local_sites
        ), $this);
    }

    public function editLocalSite()
    {
        $site_id     = ee()->input->get('site_id');
        $syncee_site = Syncee_Site::findByPk($site_id);

        if (!$syncee_site->isLocal()) {
            // TODO
        }

        $form = new Syncee_Form_Site_Local($syncee_site, $this);

        return Syncee_View::render(__FUNCTION__, array(
            'form'              => $form,
            'syncee_local_site' => $syncee_site
        ), $this);
    }

    public function editLocalSitePOST()
    {
        $site_id     = ee()->input->get('site_id');
        $syncee_site = Syncee_Site::findByPk($site_id);

        if (!$syncee_site->isLocal()) {
            // TODO
        }

        foreach ($_POST as $key => $val) {
            $syncee_site->$key = $val;
        }

        if (!$syncee_site->save()) {
            // TODO
        }

        Syncee_Helper::redirect('editLocalSite', array(
            'site_id' => $site_id
        ), $this);
    }
}