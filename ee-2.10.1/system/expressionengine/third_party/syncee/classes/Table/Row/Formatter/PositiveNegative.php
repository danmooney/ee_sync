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

class Syncee_Table_Row_Formatter_PositiveNegative extends Syncee_Table_Row_Formatter_Abstract
{
    /**
     * @param Syncee_ActiveRecord_Abstract $row
     * @return string
     */
    public function formatBasedOnRow(Syncee_ActiveRecord_Abstract $row)
    {
        $callback = $this->_callback;
        return $callback($row)
            ? 'class="positive"'
            : 'class="negative"'
        ;
    }
}