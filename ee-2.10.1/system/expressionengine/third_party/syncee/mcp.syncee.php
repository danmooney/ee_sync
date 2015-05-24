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
     * @param $method
     */
    public function handleRemoteDataApiCall($method)
    {
        // TODO
    }

    private function _renderView($template_filename, array $options = array())
    {
        ee()->view->cp_page_title = lang('syncee_module_name');

        return ee()->load->view($template_filename, $options, true);
    }
}