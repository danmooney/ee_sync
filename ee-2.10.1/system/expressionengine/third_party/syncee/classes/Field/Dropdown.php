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

class Syncee_Field_Dropdown extends Syncee_Field
{
    protected $_options = array();

    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function __toString()
    {
        $field_html = '';

        if ($label = $this->getLabel()) {
            $field_html .= form_label($label, $this->_name);
        }

        $field_html .= form_dropdown($this->_name, $this->_options, $this->_value);

        // add disabled attribute to 1st (placeholder) option
        $field_html  = preg_replace('#<option value=""#', '<option value="" disabled ', $field_html, 1);

        return $field_html;
    }
}