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

class Syncee_Mcp_Site_Group extends Syncee_Mcp_Abstract
{
    public function viewSiteGroupList()
    {
        $syncee_site_groups = Syncee_Site_Group::findAll();

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_groups' => $syncee_site_groups
        ), $this);
    }

    public function viewSiteGroup()
    {
        return Syncee_View::render(__FUNCTION__, array(

        ), $this);
    }

    public function newSiteGroup()
    {
        unset($_GET['site_group_id']);
        return $this->editSiteGroup();
    }

    public function newSiteGroupPOST()
    {
        $this->editSiteGroupPOST();
    }

    public function editSiteGroup()
    {
        $site_group_id        = ee()->input->get('site_group_id');
        $site_group_id_passed = (bool) $site_group_id;
        $syncee_site_group    = $site_group_id_passed ? Syncee_Site_Group::findByPk($site_group_id) : new Syncee_Site_Group();

        if ($syncee_site_group->isEmptyRow() && $site_group_id_passed) {
            // TODO
        }

        $ee_sites = ee()->db->get('sites')->result_object();

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_group' => $syncee_site_group,
            'ee_sites'          => $ee_sites
        ), $this);
    }

    public function editSiteGroupPOST()
    {
        $syncee_site_group        = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));
        $former_local_syncee_site = $syncee_site_group->getSiteCollection()->filterByCondition('isLocal', true);
        $new_local_syncee_site    = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => ee()->input->post('ee_site_id')), true);

        // delete syncee group map with $former_local_syncee_site
        if (!$syncee_site_group->isEmptyRow() && !$former_local_syncee_site->isEmptyRow()) {
            Syncee_Site_Group_Map::findByPk(array(
                'site_group_id' => $syncee_site_group->getPrimaryKeyValues(true),
                'site_id'       => $former_local_syncee_site->getPrimaryKeyValues(true),
            ))->delete();
        }

        $syncee_site_group->site_id = $new_local_syncee_site->getPrimaryKeyValues(true);

        foreach ($_POST as $key => $val) {
            $syncee_site_group->$key = $val;
        }

        if (!$syncee_site_group->save()) {
            // TODO
        }

        $site_group_id = $syncee_site_group->getPrimaryKeyValues(true);

        ee()->functions->redirect(Syncee_Helper::createModuleCpUrl('viewSiteGroup', array(
            'site_group_id' => $site_group_id
        )));
    }

    public function deleteSiteGroup()
    {
        $syncee_site_group = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));

        if ($syncee_site_group->isEmptyRow()) {
            // TODO
        }

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_group' => $syncee_site_group
        ), $this);
    }

    public function deleteSiteGroupPOST()
    {
        $syncee_site_group = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));

        if ($syncee_site_group->isEmptyRow()) {
            // TODO
        }

        if (!$syncee_site_group->delete()) {
            // TODO
        }

        ee()->functions->redirect(Syncee_Helper::createModuleCpUrl('viewSiteGroupList'));
    }

    public function synchronizeSiteGroup()
    {

    }

    public function viewSiteGroupSynchronizationLog()
    {

    }
}