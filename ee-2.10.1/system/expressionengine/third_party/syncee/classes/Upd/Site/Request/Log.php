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

class Syncee_Upd_Site_Request_Log extends Syncee_Upd_Abstract
{
    protected $_fields = array(
        'request_log_id' => array(
            'type'           => 'INT',
            'unsigned'       => true,
            'null'           => false,
            'auto_increment' => true
        ),
        'site_id' => array(
            'type'          => 'INT',
            'unsigned'      => true,
            'null'          => false,
        ),
        'entity_class_name' => array(
            'type'       => 'VARCHAR',
            'constraint' => 100,
            'null'       => false,
        ),
        // status code
        'code' => array(
            'type' => 'INT',
            'null' => false
        ),
        'version' => array(
            'type'       => 'VARCHAR',
            'constraint' => 25,
            'null'       => true
        ),
        'message' => array(
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => true,
        ),
        'errors' => array(
            'type'       => 'VARCHAR',
            'constraint' => 1000,
            'null'       => true
        ),
        'raw_response' => array(
            'type'  => 'LONGTEXT',
            'null'  => true,
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
        ee()->dbforge->add_key('request_id', true);
        ee()->dbforge->add_key('site_id', false);
        ee()->dbforge->add_key('entity', false);
        ee()->dbforge->create_table($this->getTableName(), true);
    }
}