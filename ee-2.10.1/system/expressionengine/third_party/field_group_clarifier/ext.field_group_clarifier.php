<?php

class Field_Group_Clarifier_Ext
{
    public $name             = 'Field Group Clarifier';
	public $version          = '1.0';
	public $description      = 'Shows the field group you are working on while adding or updating channel fields.';
	public $settings_exist   = 'n';
	public $docs_url         = '';

    public function activate_extension()
    {
        $hook_data = array(
			array(
				'class'    => ucfirst(strtolower(__CLASS__)),
				'hook'     => 'custom_field_modify_data',
				'method'   => 'getAndStoreFieldGroupName',
				'enabled'  => 'y',
				'settings' => '',
				'priority' => 9,
				'version'  => $this->version
			),
		);

		ee()->db->insert_batch('extensions', $hook_data);
    }

	public function update_extension($current = '')
	{
		return true;
	}

	public function disable_extension()
	{
		ee()->db->delete('extensions', array(
			'class' => ucfirst(strtolower(__CLASS__))
		));
	}

    public function getAndStoreFieldGroupName($field, $method, $parameters)
    {
	    static $channel_field_group_name;

	    $should_return = (
		    isset($channel_field_group_name) ||
		    $method !== 'display_settings' ||
		    (isset($_POST) && $_POST) ||
		    REQ !== 'CP' ||
		    (!isset($_GET['C']) || $_GET['C'] !== 'admin_content') ||
		    (!isset($_GET['M']) || $_GET['M'] !== 'field_edit')
	    );

	    if ($should_return) {
		    return $parameters;
	    }

		$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

	    if (!$group_id) {
		    return $parameters;
	    }

	    $channel_field_group_name = ee()->db->select('group_name')->from('field_groups')->where('group_id', $group_id)->get()->row('group_name');

	    $ee = ee();

	    if (isset($ee->cp) && method_exists($ee->cp, 'set_variable') && function_exists('lang')) {
            $is_creating_new_custom_field = !isset($_GET['field_id']);

		    $lang_key = $is_creating_new_custom_field ? 'create_new_custom_field' : 'edit_field';

            if (!lang($lang_key)) {
                if ($is_creating_new_custom_field) {
                    $action = 'Create a New Channel Field';
                } else {
                    $action = 'Edit Field';
                }
            } else {
                $action = lang($lang_key);
            }

			$ee->cp->set_variable('cp_page_title', sprintf('%s for Field Group "%s"', $action, $channel_field_group_name));
	    } elseif (isset($ee->view) && is_object($ee->view)) {
	        $ee->view->cp_page_title .= sprintf(' for Field Group "%s"', $channel_field_group_name);
	    }

	    return $parameters;
    }
}