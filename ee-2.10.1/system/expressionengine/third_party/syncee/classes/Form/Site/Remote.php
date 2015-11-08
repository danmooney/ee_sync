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

class Syncee_Form_Site_Remote extends Syncee_Form_Abstract
{
    protected $_fields = array(
        'title' => array(
            'label' => 'Label',
            'instructions' => 'Give this remote site a name so you can identify it',
            'required' => true,
        ),
        'use_https' => array(
            'label'        => 'Use HTTPS?',
            'instructions' => 'Should this remote site be called over HTTPS?',
            'type'         => 'dropdown',
            'required'     => true,
            'options'      => array(
                0 => 'No',
                1 => 'Yes'
            ),
        ),
        'basic_http_auth' => array(
            'label'        => 'Basic HTTP Auth',
            'instructions' => 'If server is protected against Basic HTTP Authorization, enter the username and password separated by a colon (":") in order to access it'
        ),
    );

    protected $_button_text_by_method = array(
        'edit' => 'Update Remote Site Settings'
    );
}