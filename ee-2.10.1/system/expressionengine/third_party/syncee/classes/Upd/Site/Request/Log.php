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
//        'site_group_id' => array(
//            'type'          => 'INT',
//            'unsigned'      => true,
//            'null'          => true,
//        ),
// REMEMBER - local_site_id can easily be changed at any time by the user!  Need to denormalize this value
//        'local_site_id' => array(
//            'type'          => 'INT',
//            'unsigned'      => true,
//            'null'          => true,
//        ),
        // remote site id
        'site_id' => array(
            'type'          => 'INT',
            'unsigned'      => true,
            'null'          => false,
        ),
        'request_direction' => array(
            'type'       => 'tinyint',
            'constraint' => '1',
            'default'    => Syncee_Site_Request_Log::REQUEST_DIRECTION_OUTBOUND,
            'null'       => false,
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
        'content_type' => array(
            'type'       => 'VARCHAR',
            'constraint' => 100,
            'null'       => true
        ),
        // the syncee version of the responder
        'version' => array(
            'type'       => 'VARCHAR',
            'constraint' => 25,
            'null'       => true
        ),
        // the syncee version of the requestor
        'request_version' => array(
            'type'       => 'VARCHAR',
            'constraint' => 25,
            'null'       => false
        ),
        'ee_version' => array(
            'type'       => 'VARCHAR',
            'constraint' => 25,
            'null'       => true
        ),
        'ip_address' => array(
            'type'       => 'VARCHAR',
            'constraint' => 100,
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
        'url' => array(
            'type'       => 'VARCHAR',
            'constraint' => 1000,
            'null'       => true
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
        ee()->dbforge->add_key('request_log_id', true);
        ee()->dbforge->add_key(array('request_direction', 'request_log_id', 'site_id'), false);
        ee()->dbforge->add_key('entity_class_name', false);
        ee()->dbforge->create_table($this->getTableName(), true);
    }
}