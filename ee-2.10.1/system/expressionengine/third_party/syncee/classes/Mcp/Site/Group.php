<?php

class Syncee_Mcp_Site_Group
{
    public function viewSiteGroupList()
    {
        $syncee_site_groups = Syncee_Site_Group::findAll();

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_groups' => $syncee_site_groups
        ));
    }

    public function viewSiteGroup()
    {

    }

    public function newSiteGroup()
    {
        $ee_sites = ee()->db->get('sites')->result_array();

        return Syncee_View::render(__FUNCTION__, array(
            'ee_sites' => $ee_sites,
        ));
    }

    public function newSiteGroupPOST()
    {
        $new_syncee_site_group = new Syncee_Site_Group();

        foreach ($_POST as $key => $val) {
            $new_syncee_site_group->$key = $val;
        }

        if (!$new_syncee_site_group->save()) {
            // TODO
        }

        $new_site_group_id = $new_syncee_site_group->getPrimaryKeyValues(true);

        ee()->functions->redirect(Syncee_Helper::createModuleCpUrl('viewSiteGroup', array(
            'site_group_id' => $new_site_group_id
        )));
    }

    public function editSiteGroup()
    {
        $syncee_site_group = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));

        if ($syncee_site_group->isEmptyRow()) {
            // TODO
        }

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_group' => $syncee_site_group
        ));
    }

    public function editSiteGroupPOST()
    {
        $syncee_site_group = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));

        if ($syncee_site_group->isEmptyRow()) {
            // TODO
        }

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
        ));
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