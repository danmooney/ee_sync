<?php

require_once dirname(__FILE__) . '/_init.php';

class Syncee_Upd
{
    const VERSION = '0.1.0';

    /**
     * @var EE
     */
    public $EE;

    public $version = self::VERSION;

    public $module_name = 'Syncee';

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

        // Add table syncee_settings
        ee()->dbforge->drop_table('syncee_settings');

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
        ee()->dbforge->create_table('syncee_settings', true);


        // Add table syncee_sites
        $sites_table_fields = array(
            'site_id' => array(
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false
            ),
            'site_url' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        );

        // Add table syncee_settings
        ee()->dbforge->drop_table('syncee_site');

        ee()->dbforge->add_field($sites_table_fields);
        ee()->dbforge->add_key('site_id', true);
        ee()->dbforge->add_key('site_url', true);
        ee()->dbforge->create_table('syncee_site');

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

        return true;
    }

    public function update()
    {
        return true; // TODO
    }
}