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

class Syncee_Mcp_Site_Remote extends Syncee_Mcp_Abstract
{
    public function viewRemoteSiteList()
    {
        $syncee_remote_sites = Syncee_Site::getRemoteSiteCollection();

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_remote_sites' => $syncee_remote_sites
        ), $this);
    }

    public function newRemoteSite()
    {
        unset($_GET['site_id']);
        return $this->editRemoteSite();
    }

    public function newRemoteSitePOST()
    {
        $encoded_payload = ee()->input->post('remote_site_settings_payload');
        $syncee_site     = Syncee_Site::getByDecodingRemoteSiteSettingsPayload($encoded_payload);

        if ($syncee_site->isEmptyRow()) {
            // TODO
        }

        if (!$syncee_site->save()) {
            // TODO
        }

        ee()->functions->redirect(Syncee_Helper::createModuleCpUrl('editRemoteSite', array(
            'site_id' => $syncee_site->getPrimaryKeyValues(true)
        )));
    }

    public function editRemoteSite()
    {
        /**
         * @var $syncee_site
         */
        $site_id        = ee()->input->get('site_id');
        $site_id_passed = (bool) $site_id;
        $syncee_site    = Syncee_Site::findByPk(ee()->input->get('site_id'));

        if ($site_id_passed) {
            if ($syncee_site->isEmptyRow() || !$syncee_site->isRemote()) {
                // TODO
            }
        }

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_remote_site' => $syncee_site
        ), $this);
    }

    public function editRemoteSitePOST()
    {
        $site_id     = ee()->input->get('site_id');
        $syncee_site = Syncee_Site::findByPk($site_id);

        if (!$syncee_site->isRemote()) {
            // TODO
        }

        foreach ($_POST as $key => $val) {
            $syncee_site->$key = $val;
        }

        if (!$syncee_site->save()) {
            // TODO
        }

        ee()->functions->redirect(Syncee_Helper::createModuleCpUrl('editRemoteSite', array(
            'site_id' => $site_id
        )));
    }

    public function newRemoteSiteToSiteGroup()
    {

    }

    public function newRemoteSiteToSiteGroupPOST()
    {

    }

    public function pingRemoteSite()
    {

    }

    public function deleteRemoteSite()
    {
        $site_id     = ee()->input->get('site_id');
        $syncee_site = Syncee_Site::findByPk($site_id);

        if ($syncee_site->isEmptyRow()) {

        }

        if (!$syncee_site->isRemote()) {
            // TODO
        }

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_remote_site' => $syncee_site
        ), $this);
    }

    public function deleteRemoteSitePOST()
    {
        $site_id     = ee()->input->get('site_id');
        $syncee_site = Syncee_Site::findByPk($site_id);

        if ($syncee_site->isEmptyRow()) {
            // TODO
        }

        if (!$syncee_site->isRemote()) {
            // TODO
        }

        if (!$syncee_site->delete()) {
            // TODO
        }

        ee()->functions->redirect(Syncee_Helper::createModuleCpUrl('viewRemoteSiteList'));
    }
}