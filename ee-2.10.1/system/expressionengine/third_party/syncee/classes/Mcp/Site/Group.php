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
    public function __construct()
    {
        parent::__construct();
        $this->_setSubjectPageTitleBasedOnSiteGroupPassedInRequest();
    }

    public function viewSiteGroupList()
    {
        $paginator          = new Syncee_Paginator_Site_Group($_GET, $this);
        $site_groups        = Syncee_Site_Group::findAll($paginator);

        return Syncee_View::render(__FUNCTION__, array(
            'paginator'          => $paginator,
            'site_groups' => $site_groups
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
        $site_group           = $site_group_id_passed ? Syncee_Site_Group::findByPk($site_group_id) : new Syncee_Site_Group();

        if ($site_group->isEmptyRow() && $site_group_id_passed) {
            show_error('Unable to find site group');
        }

        $form     = new Syncee_Form_Site_Group($site_group, $this);
        $ee_sites = ee()->db->get('sites')->result_object();

        return Syncee_View::render(__FUNCTION__, array(
            'site_group' => $site_group,
            'ee_sites'   => $ee_sites,
            'form'       => $form
        ), $this);
    }

    public function editSiteGroupPOST($redirect_method = 'editSiteGroup')
    {
        $form = new Syncee_Form_Site_Group(new Syncee_Site_Group($_POST), $this);

        if (!$form->isValid()) {
            show_error('Form errors: <pre>' . print_r($form->getErrors(), true));
        }

        $site_group = Syncee_Site_Group::findByPk($form->getValue('site_group_id'));

        $site_group->delete();

        // delete syncee group map with $former_local_syncee_site
//        if (!$site_group->isEmptyRow()) {
//            $site_group_map =
//            Syncee_Site_Group_Map::findAllByCondition(array(
//                'site_group_id' => $site_group->getPrimaryKeyValues(true),
//            ))->delete();
//        }

        $site_group = new Syncee_Site_Group($form->getValues());

        $site_ids   = array_merge((array) $form->getValue('local_site_id'), $form->getValue('remote_site_id'));
        $site_group->site_id = $site_ids;

        $site_group->save();

        $site_group_id = $site_group->getPrimaryKeyValues(true);

        Syncee_Helper::redirect($redirect_method, array(
            'site_group_id' => $site_group_id
        ), $this);
    }

    public function deleteSiteGroup()
    {
        $site_group = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));

        if ($site_group->isEmptyRow()) {
            // TODO
        }

        return Syncee_View::render(__FUNCTION__, array(
            'site_group' => $site_group
        ), $this);
    }

    public function deleteSiteGroupPOST()
    {
        $site_group = Syncee_Site_Group::findByPk(ee()->input->get('site_group_id'));

        if ($site_group->isEmptyRow()) {
            // TODO
        }

        if (!$site_group->delete()) {
            // TODO
        }

        Syncee_Helper::redirect('viewSiteGroupList', array(), $this);
    }

    private function _setSubjectPageTitleBasedOnSiteGroupPassedInRequest()
    {
        $site_group_id        = ee()->input->get('site_group_id');
        $site_group_id_passed = (bool) $site_group_id;

        if (!$site_group_id_passed) {
            return;
        }

        $site_group = Syncee_Site_Group::findByPk($site_group_id);

        if ($site_group->isEmptyRow()) {
            return;
        }

        $site_group_edit_href = Syncee_Helper::createModuleCpUrl('editSiteGroup', $site_group->getPrimaryKeyNamesValuesMap());

        Syncee_View::setPageTitleSubjectHref($site_group_edit_href);
    }
}