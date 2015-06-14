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

interface Syncee_Collection_Interface extends Syncee_Entity_Interface
{
    /**
     * @param Syncee_Site $site
     * @return void
     */
    public function setSite(Syncee_Site $site);

    /**
     * @return Syncee_Site
     */
    public function getSite();

    public function appendToCollectionAsArray(array $row);

    public function appendToCollectionAsEntity(Syncee_Entity_Interface $row);

    public function getEntityByUniqueIdentifierKeyAndValue($identifier_value, $identifier_key_override = null);
}