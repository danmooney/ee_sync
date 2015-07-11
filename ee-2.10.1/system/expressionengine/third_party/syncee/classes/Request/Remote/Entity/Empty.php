<?php
/**
 * Syncee_Request_Remote_Entity_Empty
 *
 * Used for pinging remote server with no expectation of EE data being returned
 */
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

class Syncee_Request_Remote_Entity_Empty extends Syncee_Request_Remote_Entity_Abstract
{
    public function getName()
    {
        return 'ping';
    }

    public function getCollection()
    {
        return new Syncee_Collection_Empty();
    }
}