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

class Syncee_Form_Site_Remote_New extends Syncee_Form_Abstract
{
    protected $_fields = array(
        'title' => array(
            'label' => 'Label',
            'instructions' => '',
            'required' => true,
        ),
        'remote_site_settings_payload' => array(
            'label'        => 'Settings Payload',
            'instructions' => 'Copy the settings payload from a local site on another Syncee installation and paste it into here.',
            'type'         => 'textarea',
            'required'     => true
        ),
    );

    protected $_button_text_by_method = array(
        'new' => 'Save New Remote Site'
    );
}