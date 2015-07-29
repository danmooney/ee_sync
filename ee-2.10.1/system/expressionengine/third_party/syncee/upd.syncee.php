<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Upd
{
    const VERSION = SYNCEE_VERSION;

    const MODULE_NAME = 'Syncee';

    /**
     * @var EE
     */
    public $EE;

    public $version = self::VERSION;

    public $module_name = 'Syncee';  // distinguished from self::MODULE_NAME when free version is being used; this is the property EE looks for

    public function install()
    {
        ee()->load->dbforge();

        $module_data = array(
            'module_name'        => $this->module_name,
            'module_version'     => $this->version,
            'has_cp_backend'     => 'y',
            'has_publish_fields' => 'n',
        );

        ee()->db->insert('modules', $module_data);

        $syncee_mcp                = new Syncee_Mcp();
        $syncee_mcp_action_methods = array_filter(get_class_methods($syncee_mcp), function ($method) {
            return stripos($method, 'action') === 0;
        });

        foreach ($syncee_mcp_action_methods as $syncee_mcp_action_method) {
            $action_data = array(
                'class'       => $this->module_name . '_mcp',
                'method'      => $syncee_mcp_action_method,
                'csrf_exempt' => true
            );

            ee()->db->insert('actions', $action_data);
        }

        // recursively iterate through Syncee_Upd_* classes to install tables
        $dir_iterator = new RecursiveDirectoryIterator(SYNCEE_PATH . '/classes/Upd', RecursiveDirectoryIterator::SKIP_DOTS);

        /**
         * @var $upd_obj Syncee_Upd_Abstract
         */
        foreach (new RecursiveIteratorIterator($dir_iterator) as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $class_name = Syncee_Helper::getClassNameFromPathname($file->getPathname());

            if (!$class_name) {
                continue;
            }


            $upd_obj = new $class_name();

            $upd_obj->install();
        }

        return true;
    }

    public function uninstall()
    {
        ee()->load->dbforge();

        // Unregister the module
		$module_id = ee()->db->select('module_id')
			->where('module_name', $this->module_name)
			->from('modules')
			->get()
			->row('module_id')
        ;

		if ($module_id) {
			ee()->db
                ->where('module_id', $module_id)
				->delete('module_member_groups')
            ;

			ee()->db
                ->where('module_name', $this->module_name)
				->delete('modules')
            ;
		}

		// delete actions
		ee()->db
            ->where('class', $this->module_name . '_mcp')
            ->delete('actions')
        ;

        // recursively iterate through Syncee_Upd_* classes to uninstall tables
        $dir_iterator = new RecursiveDirectoryIterator(SYNCEE_PATH . '/classes/Upd', RecursiveDirectoryIterator::SKIP_DOTS);

        /**
         * @var $upd_obj Syncee_Upd_Abstract
         */
        foreach (new RecursiveIteratorIterator($dir_iterator) as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $class_name = Syncee_Helper::getClassNameFromPathname($file->getPathname());

            if (!$class_name) {
                continue;
            }


            $upd_obj = new $class_name();

            $upd_obj->uninstall();
        }

        return true;
    }

    public function update()
    {
        return true; // TODO
    }
}