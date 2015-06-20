<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
    public function __construct()
    {
        ee()->view->cp_page_title = lang('syncee_module_name');
        Syncee_View::addStylesheets();
        Syncee_View::addScripts();
    }

    public function index()
    {
        return $this->viewSiteGroupList();
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