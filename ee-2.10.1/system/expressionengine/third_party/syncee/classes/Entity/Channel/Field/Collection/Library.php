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

class Syncee_Entity_Channel_Field_Collection_Library extends Syncee_Collection_Library_Comparator_Abstract
{
    protected $_collection_model = 'Syncee_Entity_Channel_Field_Collection';

    /**
     * Override Syncee_Collection_Library_Abstract::collectionAlreadyExistsInLibrary to test site private member as well in comparison evaluation
     * @param Syncee_Collection_Abstract $collection
     * @return bool
     */
    public function collectionAlreadyExistsInLibrary(Syncee_Collection_Abstract $collection)
    {
        /**
         * @var $collection_to_test Syncee_Entity_Channel_Collection
         * @var $collection         Syncee_Entity_Channel_Collection
         */
        $collection_exists_already = false;


        foreach ($this->_collections as $collection_to_test) {
            if ($collection_to_test->toArray(false) == $collection->toArray(false) &&
                $collection_to_test->getSite() === $collection->getSite()
            ) {
                $collection_exists_already = true;
                break;
            }
        }

        return $collection_exists_already;
    }
}