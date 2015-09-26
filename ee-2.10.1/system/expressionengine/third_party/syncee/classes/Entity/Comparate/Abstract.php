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

abstract class Syncee_Entity_Comparate_Abstract extends Syncee_Entity_Abstract implements Syncee_Entity_Comparate_Ignore_Interface
{
    protected $_active_record_class_name;

    protected $_ignored_columns_in_comparison = array();

    public function getIgnoredColumnsFromComparison()
    {
        return $this->_ignored_columns_in_comparison;
    }

    /**
     * @return Syncee_ActiveRecord_Abstract
     */
    public function getActiveRecord()
    {
        $active_record_class_name = $this->getActiveRecordClassName();
        return new $active_record_class_name();
    }

    public function getActiveRecordClassName()
    {
        return $this->_active_record_class_name;
    }
}