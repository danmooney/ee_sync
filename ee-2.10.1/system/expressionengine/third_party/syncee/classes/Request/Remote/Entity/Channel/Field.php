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

    public function getName()
    {
        return 'channel field';
    }

    public function queryDatabaseAndGenerateCollection()
    {
        $fields = ee()->db->get('channel_fields');
        $rows   = array();

        foreach ($fields->result_array() as $field) {
            if ($field['site_id'] !== $this->getRequestedEeSiteId()) {
                continue;
            }

            $rows[] = $field;
        }

        $class_name = $this->getCollectionClassName();
        return new $class_name($rows);
    }
}