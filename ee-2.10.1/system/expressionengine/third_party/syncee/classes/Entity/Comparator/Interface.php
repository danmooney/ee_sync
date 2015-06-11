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

interface Syncee_Entity_Comparator_Interface
{
    /**
     * Compare a $source and provide comparisons against $target, such that if applied to $target it would be in sync with $source
     *
     * @param Syncee_Entity_Abstract $source
     * @param Syncee_Entity_Abstract $target
     * @return Syncee_Entity_Comparison_Collection
     */
    public function compareEntities(Syncee_Entity_Abstract $source, Syncee_Entity_Abstract $target);
}