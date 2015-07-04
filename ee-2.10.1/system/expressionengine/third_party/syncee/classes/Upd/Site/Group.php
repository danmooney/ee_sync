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

class Syncee_Upd_Site_Group extends Syncee_Upd_Abstract
{
    protected $_fields = array(
        'site_group_id' => array(
            'type'           => 'INT',
            'unsigned'       => true,
            'null'           => false,
            'auto_increment' => true
        ),
        'title' => array(
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => false,
        ),
        'create_datetime' => array(
            'type'  => 'DATETIME',
            'null'  => false,
        ),
        'last_sync_datetime' => array(
            'type'  => 'DATETIME',
            'null'  => true
        ),
    );

    public function install()
    {
        ee()->dbforge->drop_table($this->getTableName());
        ee()->dbforge->add_field($this->_fields);
        ee()->dbforge->add_key('site_group_id', true);
        ee()->dbforge->create_table($this->getTableName(), true);
    }
}