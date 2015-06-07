<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
//    const SETTING_REMOTE_IP_WHITELIST = 'SETTING_REMOTE_IP_WHITELIST';

    private $_last_curl_info;

    private $_last_response_decoded;

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
        $method     = ee()->input->get('method');
        $site_id    = ee()->input->get('site_id');

        new Syncee_Request_Remote($method, $site_id);
    }

    public function makeRemoteDataApiCallToSite(Syncee_Site $remote_site, $method)
    {
        $remote_site_url = $remote_site->getSiteUrl() . "?ACT={$remote_site->action_id}&method=$method&site_id={$remote_site->site_id}";

        $ch = curl_init($remote_site_url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
        ));

        $response              = curl_exec($ch);
        $this->_last_curl_info = curl_getinfo($ch);

        $decoded_response      = json_decode($response, true);

        if (is_array($decoded_response) && isset($decoded_response['data']) && is_string($decoded_response['data'])) {
            $remote_site->rsa->getCrypt()->loadKey($remote_site->rsa->getPrivateKey());
            $decoded_response['data']     = $remote_site->rsa->getCrypt()->decrypt(base64_decode($decoded_response['data']));
            $this->_last_response_decoded = $decoded_response;
            $response = json_encode($decoded_response);
        }

        return $response;
    }

    public function addOrUpdateSetting($setting_key, $setting_value)
    {
        if (!in_array($setting_key, $this->_setting_keys)) {
            throw new Exception('Setting passed to ' . __METHOD__ . ' not in list of allowed settings.  Setting passed: ' . $setting_key);
        }

        ee()->db->delete('syncee_setting', array(
            'setting_key' => $setting_key
        ));

        ee()->db->insert('syncee_setting', array(
            'setting_key' => $setting_value
        ));
    }

    public function getLastCurlInfo()
    {
        return $this->_last_curl_info;
    }

    public function getLastResponseDecoded()
    {
        return $this->_last_response_decoded;
    }

    public function getLastResponseDataDecoded()
    {
        return is_array($this->_last_response_decoded) && isset($this->_last_response_decoded['data'])
            ? json_decode($this->_last_response_decoded['data'], true)
            : false
        ;
    }

    private function _renderView($template_filename, array $options = array())
    {
        ee()->view->cp_page_title = lang('syncee_module_name');

        return ee()->load->view($template_filename, $options, true);
    }
}