<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Mcp
{
    private $_default_method = 'viewSiteGroupList';

    private static $_real_method;

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
         * @var $file SplFileInfo
         */
        foreach (new RecursiveIteratorIterator($dir_iterator) as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            preg_match('#class ([a-z_]+)#i', $contents, $matches);

            if (!isset($matches[1])) {
                continue;
            }

            $class_name = $matches[1];
            $mcp_obj    = new $class_name();

            if (method_exists($mcp_obj, $method)) {
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