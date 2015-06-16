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
     * @return string
     */
    public function getRowModel();

    public function appendToCollectionAsArray(array $row);

    public function appendToCollectionAsEntity(Syncee_Entity_Interface $row);

    /**
     * @param mixed $identifier_value
     * @param string|null $identifier_key_override
     * @return Syncee_Entity_Abstract|bool
     */
    public function getEntityByUniqueIdentifierKeyAndValue($identifier_value, $identifier_key_override = null);

    /**
     * @param Syncee_Entity_Abstract $row
     * @return bool
     */
    public function entityAlreadyExistsInCollection(Syncee_Entity_Abstract $row);
}