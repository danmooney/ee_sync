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

abstract class Syncee_Entity_Comparate_Collection_Abstract extends Syncee_Collection_Abstract
    implements Syncee_Site_Storage_Interface
{
    /**
     * @var Syncee_Site
     */
    protected $_site;

    public function setSite(Syncee_Site $site)
    {
        $this->_site = $site;

        /**
         * @var $row Syncee_Entity_Comparate_Abstract
         */
        foreach ($this->_rows as $row) {
            $row->setSite($site);
        }

        return $this;
    }

    public function getSite()
    {
        return $this->_site;
    }

    public function getComparatorCollectionLibraryName()
    {
        return get_class($this) . '_Library';
    }

    /**
     * @return Syncee_Collection_Library_Comparator_Abstract
     */
    public function getComparatorCollectionLibrary()
    {
        $comparator_collection_library_str = $this->getComparatorCollectionLibraryName();

        return new $comparator_collection_library_str();
    }
}