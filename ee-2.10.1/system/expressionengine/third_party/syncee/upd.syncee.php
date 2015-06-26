<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Upd
{
    const VERSION = '0.1.0';

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

        // Add table syncee_setting
        ee()->dbforge->drop_table('syncee_setting');

        $settings_table_fields = array(
			'setting_key' => array(
				'type'           => 'VARCHAR',
				'constraint'     => 50,
                'null'           => false
			),
            'setting_value' => array(
                'type'           => 'VARCHAR',
                'constraint'     => 1000,
                'null'           => true
            ),
        );

        ee()->dbforge->add_field($settings_table_fields);
        ee()->dbforge->add_key('setting_key', true);
        ee()->dbforge->create_table('syncee_setting', true);

        // Add table syncee_site_group
        $site_group_table_fields = array(
            'site_group_id' => array(
                'type'           => 'INT',
                'unsigned'       => true,
                'null'           => false,
                'auto_increment' => true
            ),
            'title' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ),
            'create_datetime' => array(
                'type'  => 'DATETIME',
                'null'  => false,
            ),
            'last_sync_datetime' => array(
                'type'  => 'DATETIME',
                'null'  => true
            ),
        );

        ee()->dbforge->drop_table('syncee_site_group');
        ee()->dbforge->add_field($site_group_table_fields);
        ee()->dbforge->add_key('site_group_id', true);
        ee()->dbforge->create_table('syncee_site_group', true);

        // Add table syncee_site
        $sites_table_fields = array(
            'site_id' => array(
                'type'           => 'INT',
                'unsigned'       => true,
                'null'           => false,
                'auto_increment' => true
            ),
            'site_url' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ),
            'site_host' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ),
            'ee_site_id' => array(
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false
            ),
            // Just an easy way for a user to refer to it (should probably only be used for remote sites)
            'title' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ),
            'is_local' => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ),
            'use_https' => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ),
            'ip_whitelist' => array(
                'type'           => 'VARCHAR',
                'constraint'     => 1000,
                'null'           => true
            ),
            'public_key' => array(
                'type'     => 'text',
                'null'     => false
            ),
            'private_key' => array(
                'type'     => 'text',
                'null'     => true // in the case of remote sites
            ),
            // remote request action id for Syncee_Mcp::actionHandleRemoteDataApiCall
            'action_id' => array(
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false
            ),
            // for local sites
            'requests_from_remote_sites_enabled' => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ),
            'create_datetime' => array(
                'type'  => 'DATETIME',
                'null'  => false,
            ),
        );

        ee()->dbforge->drop_table('syncee_site');

        ee()->dbforge->add_field($sites_table_fields);
        ee()->dbforge->add_key('site_id', true);
        ee()->dbforge->create_table('syncee_site');

        // Add table syncee_site_group_map
        $site_group_map_fields = array(
            'site_group_id' => array(
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false
            ),
            'site_id' => array(
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false
            ),
        );

        ee()->dbforge->drop_table('syncee_site_group_site_map');
        ee()->dbforge->add_field($site_group_map_fields);
        ee()->dbforge->add_key(array('site_group_id', 'site_id'), true);
        ee()->dbforge->create_table('syncee_site_group_site_map', true);

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

        ee()->dbforge->drop_table('syncee_setting');
        ee()->dbforge->drop_table('syncee_site');
        ee()->dbforge->drop_table('syncee_site_group_site_map');

        return true;
    }

    public function update()
    {
        return true; // TODO
    }
}