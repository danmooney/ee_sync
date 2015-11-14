<?php

/**
 * Field Group Clarifier Extension Class
 *
 * @package   FieldGroupClarifier
 * @author    Dan Mooney <dan.r.mooney@gmail.com>
 * @copyright Copyright (c) Dan Mooney
 */
class Field_Group_Clarifier_Ext
{
    private static $_channel_field_group_name;

    public $name             = 'Field Group Clarifier';
	public $version          = '1.0';
	public $description      = 'Shows the field group you are working on while adding or updating channel fields.';
	public $settings_exist   = 'n';
	public $docs_url         = 'https://devot-ee.com/add-ons/field-group-clarifier';

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
	    $should_return = (
		    isset(self::$_channel_field_group_name) ||
		    $method !== 'display_settings' ||
		    (isset($_POST) && $_POST) ||
		    REQ !== 'CP' ||
		    (!isset($_GET['C']) || !in_array($_GET['C'], array('admin_content', 'channels'))) ||
		    (!isset($_GET['M']) || !in_array($_GET['M'], array('field_edit', 'fields')))
	    );

	    if ($should_return) {
		    return $parameters;
	    }

        if (isset($_GET['group_id'])) {
		    $group_id = $_GET['group_id'];
        } elseif (isset($field) && is_object($field) && isset($field->settings) && is_array($field->settings) && isset($field->settings['group_id'])) {
            $group_id = $field->settings['group_id'];
        } elseif (isset($_SERVER['REQUEST_URI']) && preg_match('#/fields/create/(\d+)#', $_SERVER['REQUEST_URI'], $matches)) {
            $group_id = $matches[1];
        }

	    if (!isset($group_id)) {
		    return $parameters;
	    }

	    $channel_field_group_name = self::$_channel_field_group_name = ee()->db->select('group_name')->from('field_groups')->where('group_id', $group_id)->get()->row('group_name');

        if (!$channel_field_group_name) {
            // if channel field group name is missing for some reason, set the static variable so we don't try again
            self::$_channel_field_group_name = '';
            return $parameters;
        }

	    $ee = ee();

        if (isset($_GET['field_id'])) {
            $field_id = $_GET['field_id'];
        } elseif (isset($field) && is_object($field) && isset($field->settings) && is_array($field->settings) && isset($field->settings['field_id'])) {
            $field_id = $field->settings['field_id'];
        }

        $is_creating_new_custom_field = !isset($field_id);

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

	    if (isset($ee->cp) && method_exists($ee->cp, 'set_variable') && function_exists('lang')) {
			$ee->cp->set_variable('cp_page_title', sprintf('%s for Field Group "%s"', $action, $channel_field_group_name));
	    } elseif (isset($ee->view) && is_object($ee->view)) {
            $cp_page_title_not_set_yet_and_will_most_likely_be_overwritten_later = !isset($ee->view->cp_page_title) || empty($ee->view->cp_page_title);

            if ($cp_page_title_not_set_yet_and_will_most_likely_be_overwritten_later) {
                $lang = ee()->lang;
                $lang->language[$lang_key] = sprintf('%s for Field Group "%s"', $action, $channel_field_group_name);

                if ($lang_key === 'create_new_custom_field') {
                    $lang->language['create_field'] = $lang->language[$lang_key];
                }
            } else {
	            $ee->view->cp_page_title .= sprintf(' for Field Group "%s"', $channel_field_group_name);
            }
	    }

	    return $parameters;
    }
}