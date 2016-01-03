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

class Syncee_Request_Remote_Entity_Channel_Field extends Syncee_Request_Remote_Entity_Abstract
{
    protected $_collection_class_name = 'Syncee_Entity_Channel_Field_Collection';

    protected $_references = array(
        'site_id'              => 'Syncee_Request_Remote_Entity_Site',
        'group_id'             => 'Syncee_Request_Remote_Entity_Channel_Field_Group',
        'field_pre_channel_id' => 'Syncee_Request_Remote_Entity_Channel',
        'field_pre_field_id'   => 'Syncee_Request_Remote_Entity_Channel_Field',
    );

    public function getName()
    {
        return 'channel field';
    }

    public function queryDatabaseAndGenerateCollection()
    {
        $fields        = ee()->db->get('channel_fields')->result_array();
        $field_formats = ee()->db->select('field_id, field_fmt')->from('field_formatting')->get()->result_array();
        $rows          = array();

        foreach ($fields as $field) {
            if ($field['site_id'] !== $this->getRequestedEeSiteId()) {
                continue;
            }

            $field['field_formats'] = array_filter($field_formats, function ($field_format) use ($field) {
                return $field_format['field_id'] === $field['field_id'];
            });

            $rows[] = $field;
        }

        $class_name = $this->getCollectionClassName();
        return new $class_name($rows);
    }
}