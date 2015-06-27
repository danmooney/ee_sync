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

interface Syncee_Request_Remote_Entity_Interface
{
    /**
     * Get the name of the entity
     * @return string
     */
    public function getName();

    /**
     * Fetch data and return collection
     * TODO - separate into two distinct functions?
     * @return Syncee_Collection_Interface
     */
    public function getCollection();

    /**
     * @param $ee_site_id
     */
    public function setEeSiteId($ee_site_id);

    /**
     * @return int
     */
    public function getEeSiteId();

    /**
     * @return string
     */
    public function getCollectionClassName();
}