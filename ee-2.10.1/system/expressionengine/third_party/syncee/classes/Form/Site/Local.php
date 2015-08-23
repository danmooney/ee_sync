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

class Syncee_Form_Site_Local extends Syncee_Form_Abstract
{
    protected $_fields = array(
        'requests_from_remote_sites_enabled' => array(
            'label' => 'MASTER OVERRIDE',
            'instructions' => 'In the OFF position, ABSOLUTELY NO remote sites are allowed to call this local site; the response will be 403 Forbidden.  Set to "ON" to allow some or all remote sites to call this local site (depending on the settings below).',
            'type' => 'dropdown',
            'options' => array(
                0 => 'Off',
                1 => 'On'
            ),
        ),
        'ip_whitelist' => array(
            'label' => 'IP Whitelist', // TODO - Add multiple inputs for this field (and lookup how to validate/convert CIDR ranges)
            'instructions' => 'Instructions: Enter one IP per line.  CIDR notation will not be converted to IP ranges.<br><br>If left empty by default with master override set to "ON", any remote site can make requests to this local site and view its encrypted responses.',
            'type'  => 'textarea',
        ),
    );

    protected $_button_text_by_method = array(
        'edit' => 'Update Local Site Settings',
    );
}