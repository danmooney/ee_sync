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

interface Syncee_Collection_Library_Interface
{
    public function appendToLibraryAsArray(array $collection);

    public function appendToLibraryAsCollection(Syncee_Collection_Abstract $collection);

    /**
     * Does the collection passed currently exist in the library?
     * @param Syncee_Collection_Abstract $collection
     * @return bool
     */
    public function collectionAlreadyExistsInLibrary(Syncee_Collection_Abstract $collection);
}