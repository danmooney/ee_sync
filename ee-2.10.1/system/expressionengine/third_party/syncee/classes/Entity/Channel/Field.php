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

class Syncee_Entity_Channel_Field extends Syncee_Entity_Comparate_Abstract
{
    protected $_hidden_columns_in_comparison = array(
        'field_formats',
    );

    protected $_ignored_columns_in_comparison = array(

    );

    protected $_active_record_class_name = 'Syncee_Channel_Field';

    protected $_unique_identifier_key = 'field_name';
}