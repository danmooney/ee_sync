<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Upd
{
    /**
     * @var EE
     */
    public $EE;

    public $version = '0.1.0';

    public $module_name = 'Syncee';

    public $remote_data_fetch_action = 'handleRemoteDataApiCall';

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

        $action_data = array(
            'class'  => $this->module_name . '_mcp',
            'method' => $this->remote_data_fetch_action
        );

        ee()->db->insert('actions', $action_data);

//        ee()->dbforge->drop_table('syncee_settings');

        return true;
    }

    public function uninstall()
    {
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
            ->where('class', $this->module_name)
            ->delete('actions')
        ;

        ee()->dbforge->drop_table('syncee_settings');

        return true;
    }

    public function update()
    {
        return true; // TODO
    }
}