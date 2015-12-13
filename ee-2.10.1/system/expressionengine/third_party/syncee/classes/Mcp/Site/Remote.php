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
        $paginator           = new Syncee_Paginator_Site_Remote($_GET, $this);
        $syncee_remote_sites = Syncee_Site::getRemoteSiteCollection($paginator);

        return Syncee_View::render(__FUNCTION__, array(
            'paginator'           => $paginator,
            'syncee_remote_sites' => $syncee_remote_sites
        ), $this);
    }

    public function newRemoteSite()
    {
        unset($_GET['site_id']);

        $form = new Syncee_Form_Site_Remote_New(null, $this);

        return Syncee_View::render(__FUNCTION__, array(
            'form' => $form
        ), $this);
    }

    public function newRemoteSitePOST()
    {
        $form = new Syncee_Form_Site_Remote_New(new Syncee_Site($_POST), $this);

        if (!$form->isValid()) {
            show_error('Form errors: <pre>' . print_r($form->getErrors(), true));
        }

        $encoded_payload = $form->getValue('remote_site_settings_payload');
        $syncee_site     = Syncee_Site::getByDecodingRemoteSiteSettingsPayload($encoded_payload);

        $syncee_site->assign($form->getValues());

        $sites_with_same_values = Syncee_Site::findAllByCondition(array(
            'ee_site_id' => $syncee_site->ee_site_id,
            'site_host'  => $syncee_site->site_host,
        ));

        if (count($sites_with_same_values)) {
            if ($sites_with_same_values[0]->is_local) {
                Syncee_Helper_Flashdata::setFlashData('The remote site you\'re trying to save exists as a local site on this machine.', 'error');
            } else {
                Syncee_Helper_Flashdata::setFlashData('The remote site you\'re trying to save already exists.', 'error');
            }

            Syncee_Helper::redirect('viewRemoteSiteList', array(), $this, false);
        }

        $syncee_site->save();

        Syncee_Helper::redirect('editRemoteSite', array(
            'site_id' => $syncee_site->getPrimaryKeyValues(true)
        ), $this);
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

        $form = new Syncee_Form_Site_Remote($syncee_site, $this);

        return Syncee_View::render(__FUNCTION__, array(
            'form'               => $form,
            'syncee_remote_site' => $syncee_site
        ), $this);
    }

    public function editRemoteSitePOST()
    {
        $form = new Syncee_Form_Site_Remote(new Syncee_Site($_POST), $this);

        if (!$form->isValid()) {
            show_error('Form errors: <pre>' . print_r($form->getErrors(), true));
        }

        $syncee_site = new Syncee_Site($form->getValues());

        $syncee_site->save();

        Syncee_Helper::redirect('editRemoteSite', array(
            'site_id' => $syncee_site->getPrimaryKeyValues(true)
        ), $this);
    }

    public function newRemoteSiteToSiteGroup()
    {

    }

    public function newRemoteSiteToSiteGroupPOST()
    {

    }

    public function pingRemoteSitePOST()
    {
        $site_id     = ee()->input->get('site_id');
        $syncee_site = Syncee_Site::findByPk($site_id);

        $request     = new Syncee_Request();
        $request_log = new Syncee_Site_Request_Log();
        $ping_entity = new Syncee_Request_Remote_Entity_Empty();
        $response    = $request->makeEntityCallToSite($syncee_site, $ping_entity, $request_log);

        $diagnosis   = new Syncee_Site_Request_Log_Diagnosis($request_log);

        header('Content-type: text/javascript');

        echo json_encode(array(
            'diagnoses'       => $diagnosis->getDiagnoses(),
            'request_log_url' => Syncee_Helper::createModuleCpUrl('viewRequestLog', $request_log->getPrimaryKeyNamesValuesMap()),
            'response'        => $response->getRawResponse(),
            'url'             => $request->getCurlHandle()->url,
        ));
        exit(0);
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

        if (!$syncee_site->delete(array(), false)) {
            // TODO
        }

        Syncee_Helper::redirect('viewRemoteSiteList', array(), $this);
    }
}