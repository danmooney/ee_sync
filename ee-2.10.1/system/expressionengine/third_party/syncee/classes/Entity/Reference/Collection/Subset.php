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

class Syncee_Entity_Reference_Collection_Subset extends Syncee_Collection_Generic implements Syncee_Site_Storage_Interface
{
    /**
     * @var Syncee Site
     */
    protected $_site;

    protected $_row_model = 'Syncee_Entity_Generic';

    protected $_use_strict_row_model_enforcement_when_appending = false;

    public function setSite(Syncee_Site $site)
    {
        $this->_site = $site;
    }

    public function getSite()
    {
        return $this->_site;
    }
}