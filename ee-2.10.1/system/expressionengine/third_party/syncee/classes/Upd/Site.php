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

class Syncee_Upd_Site extends Syncee_Upd_Abstract
{
    protected $_fields = array(
        'site_id' => array(
            'type'           => 'INT',
            'unsigned'       => true,
            'null'           => false,
            'auto_increment' => true
        ),
        'site_url' => array(
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => false,
        ),
        'site_host' => array(
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => false,
        ),
        'ee_site_id' => array(
            'type'     => 'INT',
            'unsigned' => true,
            'null'     => false
        ),
        // Just an easy way for a user to refer to it (should probably only be used for remote sites)
        'title' => array(
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => true,
        ),
        'is_local' => array(
            'type'       => 'TINYINT',
            'constraint' => 1,
            'null'       => false,
        ),
        'use_https' => array(
            'type'       => 'TINYINT',
            'constraint' => 1,
            'null'       => false,
        ),
        'ip_whitelist' => array(
            'type'           => 'VARCHAR',
            'constraint'     => 1000,
            'null'           => true
        ),
        'basic_http_auth'  => array(
            'type'           => 'VARCHAR',
            'constraint'     => 255,
            'null'           => true
        ),
        'public_key' => array(
            'type'     => 'text',
            'null'     => false
        ),
        'private_key' => array(
            'type'     => 'text',
            'null'     => false  // private key has to be on both target and source sites... there's just no way to do it otherwise
        ),
        // remote request action id for Syncee_Mcp::actionHandleRemoteDataApiCall
        'action_id' => array(
            'type'     => 'INT',
            'unsigned' => true,
            'null'     => false
        ),
        // for local sites
        'requests_from_remote_sites_enabled' => array(
            'type'       => 'TINYINT',
            'constraint' => 1,
            'null'       => true,
        ),
        'create_datetime' => array(
            'type'  => 'DATETIME',
            'null'  => false,
        ),
    );

    public function install()
    {
        ee()->dbforge->drop_table($this->getTableName());
        ee()->dbforge->add_field($this->_fields);
        ee()->dbforge->add_key('site_id', true);
        ee()->dbforge->create_table($this->getTableName(), true);
    }
}