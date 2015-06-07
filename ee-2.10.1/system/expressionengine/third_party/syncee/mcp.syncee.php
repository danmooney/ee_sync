<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
    public function index()
    {
        return $this->_renderView(__FUNCTION__);
    }

    /**
     * The ACT action for fetching channel data from remote source
     */
    public function actionHandleRemoteDataApiCall()
    {
        $entity_str        = ee()->input->get('entity');
        $site_id           = ee()->input->get('site_id');

        // add back module name that was removed for security reasons
        $entity_class_name = Syncee_Upd::MODULE_NAME . '_' . $entity_str;

        new Syncee_Request_Remote(new $entity_class_name(), $site_id);
    }

    private function _renderView($template_filename, array $options = array())
    {
        ee()->view->cp_page_title = lang('syncee_module_name');

        return ee()->load->view($template_filename, $options, true);
    }
}