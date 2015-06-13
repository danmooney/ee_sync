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

class Syncee_Entity_Channel extends Syncee_Entity_Abstract
{
    /**
     * @var Syncee_Entity_Channel_Field_Collection
     */
    private $_fields;

    protected $_ignored_columns_in_comparison = array(
        'channel_url'
    );

    public function __construct(array $row = array(), $is_new = true) // TODO - is entity going to really extend active record.  It sorta makes sense.  But channel logic is way more complicated and is based in multiple tables.
    {
        if (isset($row['fields'])) {
            $this->_fields = new Syncee_Entity_Channel_Field_Collection($row['fields']);
            unset($row['fields']);
        }

        parent::__construct($row, $is_new);
    }

    public function getFields()
    {
        return $this->_fields;
    }
}