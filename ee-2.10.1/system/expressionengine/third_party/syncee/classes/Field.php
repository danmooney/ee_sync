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

class Syncee_Field
{
    protected $_label;

    protected $_instructions;

    protected $_name;

    protected $_type;

    protected $_value;

    protected $_required;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $set_method_name = 'set' . $key;

            if (method_exists($this, $set_method_name)) {
                $this->$set_method_name($value);
            }
        }
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function isValid()
    {

    }

    public function __toString()
    {
        $field_html = '';

        if ($label = $this->getLabel()) {
            $field_html .= form_label($label, $this->_name);
        }

        $field_type = $this->_type ?: 'text';

        $field_type_function_name = 'form_' . $field_type;

        if (!function_exists($field_type_function_name)) {
            $field_type_function_name = 'form_input';
        }

        $field_html .= $field_type_function_name($this->_name, $this->_value);

        return $field_html;
    }
}