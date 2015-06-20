<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
    private $_default_method = 'viewSiteGroupList';

    public function __construct()
    {
        ee()->view->cp_page_title = lang('syncee_module_name');
        Syncee_View::addStylesheets();
        Syncee_View::addScripts();

        if (!ee()->input->get('method')) {
            $_GET['method'] = $this->_default_method;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['method'])) {
            $_GET['method'] .= 'POST';
        }
    }

    public function viewSiteGroupList()
    {
        $ee_sites           = ee()->db->get('sites')->result_array();
        $syncee_sites       = Syncee_Site::findAll();
        $syncee_site_groups = Syncee_Site_Group::findAll();

        return Syncee_View::render(__FUNCTION__, array(
            'ee_sites'           => $ee_sites,
            'syncee_sites'       => $syncee_sites,
            'syncee_site_groups' => $syncee_site_groups
        ));
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

    public function viewSiteGroup()
    {

    }

    public function newSiteToSiteGroup()
    {

    }

    public function synchronizeSiteGroup()
    {

    }

    public function viewSiteGroupSynchronizationLog()
    {

    }

    /**
     * The ACT action for fetching channel data from remote source
     */
    public function actionHandleRemoteDataApiCall()
    {
        $entity_str        = ee()->input->get('entity');
        $site_id           = ee()->input->get('site_id');

        // TODO - figure out how to do this best.  Keep EE multi-site builds in mind!  Of course remove the ee_site_id fallback ternary operator in near future!
        $ee_site_id        = ee()->input->get('ee_site_id') ?: 1;

        // add back module name that was removed for security reasons
        $entity_class_name = Syncee_Upd::MODULE_NAME . '_' . $entity_str;

        /**
         * @var $entity Syncee_Request_Remote_Entity_Interface
         */
        $entity = new $entity_class_name();
        $entity->setEeSiteId($ee_site_id);

        new Syncee_Request_Remote($entity, $site_id);
    }
}