<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
//    const SETTING_REMOTE_IP_WHITELIST = 'SETTING_REMOTE_IP_WHITELIST';

    private $_last_curl_info;

    private $_setting_keys = array(
//        self::SETTING_REMOTE_IP_WHITELIST
    );

    public function index()
    {
        return $this->_renderView(__FUNCTION__);
    }

    /**
     * The ACT action for fetching channel data from remote source
     */
    public function actionHandleRemoteDataApiCall()
    {
        $method    = ee()->input->get('method');
        $site_id   = ee()->input->get('site_id');

        new Syncee_Remote_Request($method, $site_id);
    }

    public function makeRemoteDataApiCallToSite(Syncee_Site $remote_site, $method)
    {
        // TODO - action id needs to be passed in a private key or something
        $handle_remote_data_api_call_action_id = ee()->db->select('action_id')->from('actions')->where('method', 'actionHandleRemoteDataApiCall')->get()->row('action_id');
        $remote_site_url                       = $remote_site->getSiteUrl() . "?ACT=$handle_remote_data_api_call_action_id&method=$method&site_id={$remote_site->site_id}";

        $ch = curl_init($remote_site_url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
        ));

        $response              = curl_exec($ch);
        $this->_last_curl_info = curl_getinfo($ch);

        return $response;
    }

    public function addOrUpdateSetting($setting_key, $setting_value)
    {
        if (!in_array($setting_key, $this->_setting_keys)) {
            throw new Exception('Setting passed to ' . __METHOD__ . ' not in list of allowed settings.  Setting passed: ' . $setting_key);
        }

        ee()->db->delete('syncee_settings', array(
            'setting_key' => $setting_key
        ));

        ee()->db->insert('syncee_settings', array(
            'setting_key' => $setting_value
        ));
    }

    public function getLastCurlInfo()
    {
        return $this->_last_curl_info;
    }

    private function _renderView($template_filename, array $options = array())
    {
        ee()->view->cp_page_title = lang('syncee_module_name');

        return ee()->load->view($template_filename, $options, true);
    }
}