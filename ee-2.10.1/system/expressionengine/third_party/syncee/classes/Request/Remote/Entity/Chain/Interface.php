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

interface Syncee_Request_Remote_Entity_Chain_Interface extends Syncee_Request_Remote_Entity_Interface
{
    /**
     * Get the next request if chained in constructor
     * @return Syncee_Request_Remote_Entity_Chain_Interface|boolean false
     */
    public function getNextRemoteEntityRequestInChain();

    /**
     * @param Syncee_Request_Remote_Entity_Chain_Interface $remote_entity_request
     * @return $this|boolean false
     */
    public function appendRemoteEntityRequestToChain(Syncee_Request_Remote_Entity_Chain_Interface $remote_entity_request);
}