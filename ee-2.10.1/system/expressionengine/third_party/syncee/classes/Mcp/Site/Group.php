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
        $paginator          = new Syncee_Paginator_Site_Group($_GET, $this);
        $syncee_site_groups = Syncee_Site_Group::findAll($paginator);

        return Syncee_View::render(__FUNCTION__, array(
            'paginator'          => $paginator,
            'syncee_site_groups' => $syncee_site_groups
        ), $this);
    }

    public function synchronizeSiteGroup()
    {
        $site_group_id     = ee()->input->get('site_group_id');
        $syncee_site_group = Syncee_Site_Group::findByPk($site_group_id);

        if ($syncee_site_group->isEmptyRow()) {
            // TODO
        }

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_group' => $syncee_site_group
        ), $this);
    }

    // TODO - this has to be ajaxified (possibly with websockets to show progress to user)
    public function synchronizeSiteGroupChannels()
    {
        $site_group_id     = ee()->input->get('site_group_id');
        $syncee_site_group = Syncee_Site_Group::findByPk($site_group_id);

        $channel_comparison_library = $syncee_site_group->getSiteCollection()->getChannelComparisonCollectionLibrary()/*->getNonEmptyComparisonCollectionLibrary()*/;

        // sort collections alphabetically by source site primary key.
        // this is to have a known and predictable way to iterate over collections and get everything in the right order.
        $channel_comparison_library->sortByCallback(function (Syncee_Entity_Comparison_Collection $a, Syncee_Entity_Comparison_Collection $b) {
            return $a->getSource()->getSite()->getPrimaryKeyValues(true) - $b->getSource()->getSite()->getPrimaryKeyValues(true);
        });

        $request_log_collection     = $syncee_site_group->getSiteCollection()->getRequestLogCollection();

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_group'          => $syncee_site_group,
            'entity_comparison_library' => $channel_comparison_library,
            'request_log_collection'     => $request_log_collection
        ), $this);
    }

    public function newSiteGroup()
    {
        unset($_GET['site_group_id']);
        return $this->editSiteGroup();
    }

    public function newSiteGroupPOST()
    {
        $this->editSiteGroupPOST('viewSiteGroupList');
    }

    public function editSiteGroup()
    {
        $site_group_id        = ee()->input->get('site_group_id');
        $site_group_id_passed = (bool) $site_group_id;
        $syncee_site_group    = $site_group_id_passed ? Syncee_Site_Group::findByPk($site_group_id) : new Syncee_Site_Group();

        if ($syncee_site_group->isEmptyRow() && $site_group_id_passed) {
            show_error('Unable to find site group');
        }

        $form     = new Syncee_Form_Site_Group($syncee_site_group, $this);
        $ee_sites = ee()->db->get('sites')->result_object();

        return Syncee_View::render(__FUNCTION__, array(
            'syncee_site_group' => $syncee_site_group,
            'ee_sites'          => $ee_sites,
            'form'              => $form
        ), $this);
    }

    public function editSiteGroupPOST($redirect_method = 'editSiteGroup')
    {
        $form = new Syncee_Form_Site_Group(new Syncee_Site_Group($_POST), $this);

        if (!$form->isValid()) {
            show_error('Form errors: <pre>' . print_r($form->getErrors(), true));
        }

        $syncee_site_group = Syncee_Site_Group::findByPk($form->getValue('site_group_id')); // TODO - use $form->getValue('site_group_id')

        $syncee_site_group->delete();

        // delete syncee group map with $former_local_syncee_site
//        if (!$syncee_site_group->isEmptyRow()) {
//            $syncee_site_group_map =
//            Syncee_Site_Group_Map::findAllByCondition(array(
//                'site_group_id' => $syncee_site_group->getPrimaryKeyValues(true),
//            ))->delete();
//        }

        $syncee_site_group = new Syncee_Site_Group($form->getValues());

        $site_ids                   = array_merge((array) $form->getValue('local_site_id'), $form->getValue('remote_site_id'));
        $syncee_site_group->site_id = $site_ids;

        $syncee_site_group->save();

        $site_group_id = $syncee_site_group->getPrimaryKeyValues(true);

        Syncee_Helper::redirect($redirect_method, array(
            'site_group_id' => $site_group_id
        ), $this);
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

        Syncee_Helper::redirect('viewSiteGroupList', array(), $this);
    }

    public function viewSiteGroupSynchronizationLog()
    {

    }
}