<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
    private $_default_method = 'viewSiteGroupList';

    private static $_real_method;

    public function __construct()
    {
        // all of the logic below is for the control panel context, not when in the context of an action (AKA remote site API call)
        if (REQ !== 'CP') {
            return;
        }

        Syncee_View::setPageTitle(lang('syncee_module_name'));
        Syncee_View::addStylesheets();
        Syncee_View::addScripts();

        if (!ee()->input->get('method')) {
            $_GET['method'] = $this->_default_method;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['method'])) {
            $_GET['method'] .= 'POST';
        }

        // Set method to proxy and save real method so EE will execute the method and not display "Requested page not found" error
        self::$_real_method = $_GET['method'];
        $_GET['method']     = 'proxy';
    }

    /**
     * Proxy and overload without error
     * @return string
     */
    public function proxy()
    {
        $_GET['method'] = self::$_real_method;
        return $this->__call($_GET['method'], array());
    }

    /**
     * Route to separate control panel classes based on component so we don't have a billion methods in here
     * @param $method
     * @param $args
     * @return string
     */
    public function __call($method, $args)
    {
        $dir_iterator = new RecursiveDirectoryIterator(SYNCEE_PATH . '/classes/Mcp', RecursiveDirectoryIterator::SKIP_DOTS);

        /**
         * @var $file    SplFileInfo
         * @var $mcp_obj Syncee_Mcp_Abstract
         */
        foreach (new RecursiveIteratorIterator($dir_iterator) as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $class_name = Syncee_Helper::getClassNameFromPathname($file->getPathname());

            if (!$class_name) {
                continue;
            }

            $mcp_obj = new $class_name();

            if (method_exists($mcp_obj, $method)) {
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                   $this->_runLocalSiteCleanup();
                }

                $mcp_obj->setCalledMethod($method);
                $return_value = $mcp_obj->$method();
                break;
            }
        }

        return isset($return_value)
            ? $return_value
            : lang('requested_page_not_found')
        ;
    }

    /**
     * The ACT action for fetching channel data from remote source
     */
    public function actionHandleRemoteDataApiCall()
    {
        $entity_str        = ee()->input->get('entity');
        $ee_site_id        = ee()->input->get('ee_site_id');

        // add back module name that was removed for security reasons
        $entity_class_name = Syncee_Upd::MODULE_NAME . '_' . $entity_str;

        /**
         * @var $site Syncee_Site
         * @var $entity Syncee_Request_Remote_Entity_Interface
         */
        $site   = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => $ee_site_id), true);

        $entity = new $entity_class_name();
        $entity->setRequestedEeSiteId($ee_site_id);

        new Syncee_Request_Remote($site, $entity);
    }

    private function _runLocalSiteCleanup()
    {
        $syncee_site_collection        = Syncee_Site::findAll();
        $sites_with_current_local_host = $syncee_site_collection->filterByCondition(array('site_host' => $_SERVER['HTTP_HOST']));
        $local_ee_sites                = ee()->db->get('sites')->result_object();

        $discrepancy_exists_between_local_syncee_sites_and_local_ee_sites = count($local_ee_sites) !== count($sites_with_current_local_host);

        if (!$discrepancy_exists_between_local_syncee_sites_and_local_ee_sites) {
            return;
        }

        foreach ($local_ee_sites as $local_ee_site) {
            $corresponding_local_syncee_site = $sites_with_current_local_host->filterByCondition(array('ee_site_id' => $local_ee_site->site_id), true);
            if ($corresponding_local_syncee_site->isEmptyRow()) {
                $corresponding_local_syncee_site->ee_site_id                         = $local_ee_site->site_id;
                $corresponding_local_syncee_site->is_local                           = true;
                $corresponding_local_syncee_site->site_url                           = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
                $corresponding_local_syncee_site->requests_from_remote_sites_enabled = false;
                $corresponding_local_syncee_site->save();
            }
        }

        // re-evaluate $sites_with_current_local_host
        $sites_with_current_local_host = $syncee_site_collection->filterByCondition(array('site_host' => $_SERVER['HTTP_HOST']));

        /**
         * @var $site_with_current_local_host Syncee_ActiveRecord_Abstract
         */
        foreach ($sites_with_current_local_host as $site_with_current_local_host) {
            $corresponding_local_ee_site = null;
            foreach ($local_ee_sites as $local_ee_site) {
                if ($local_ee_site->site_id === $site_with_current_local_host->ee_site_id) {
                    $corresponding_local_ee_site = $local_ee_site;
                    break;
                }
            }

            if (!$corresponding_local_ee_site) {
                $site_with_current_local_host->delete();
            }
        }
    }
}