<?php

if (!defined('SYNCEE_PATH')) {
    $current_dir = dirname(__FILE__);
    $i           = 1;

    while (($ancestor_realpath = realpath($current_dir . str_repeat('/..', $i++)) . '/_init.php') && !is_readable($ancestor_realpath)) {
        $is_at_root = substr($ancestor_realpath, -1) === PATH_SEPARATOR;
        if ($is_at_root) {
            break;
        }
    }

    if (!is_readable($ancestor_realpath)) {
        show_error('Could not find _init.php for module');
    }

    require_once $ancestor_realpath;
}

class Syncee_Request_Remote_Entity_Channel extends Syncee_Request_Remote_Entity_Abstract
{
    protected $_collection_class_name = 'Syncee_Entity_Channel_Collection';

    public function getCollection()
    {
        $channels     = ee()->db->get('channels');
        $field_groups = ee()->db->get('field_groups');
        $fields       = ee()->db->get('channel_fields');

        $rows = array();

        foreach ($channels->result_array() as $channel) {
            if (intval($channel['site_id']) !== $this->_ee_site_id) {
                continue;
            }

            $channel['fields'] = array();

            foreach ($fields->result_array() as $field) {
                if ($field['group_id'] === $channel['field_group']) {
                    $channel['fields'][] = array(
                        'field_id'    => $field['field_id'],
                        'field_name'  => $field['field_name'],
                        'field_label' => $field['field_label'],
                        'field_type'  => $field['field_type']
                    );
                }
            }

            $channel['field_group_name'] = '';
            foreach ($field_groups->result_array() as $field_group) {
                if ($field_group['group_id'] === $channel['field_group']) {
                    $channel['field_group_name'] = $field_group['group_name'];
                    break;
                }
            }

            $rows[] = $channel;
        }

        $class_name = $this->getCollectionClassName();
        return new $class_name($rows);
    }
}