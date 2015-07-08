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
    protected $_errors = array();

    protected $_label;

    protected $_instructions;

    protected $_name;

    protected $_type = 'input';

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

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function isValid()
    {
        $this->_errors =  array();
        $errors        =& $this->_errors;

        if ($this->getRequired() && (string) $this->getValue() === '') {
            $errors[] = Syncee_Field_Error::FIELD_ERROR_REQUIRED_BUT_EMPTY;
        }

        return empty($errors);
    }

    public function setRequired($required)
    {
        $this->_required = $required;
    }

    public function getRequired()
    {
        return $this->_required;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function setInstructions($instructions)
    {
        $this->_instructions = $instructions;
    }

    public function getInstructions()
    {
        return $this->_instructions;
    }

    public function __toString()
    {
        $field_html  = '';
        $field_html .= '<tr>';
        $field_html .= '<td>';

        if ($label = $this->getLabel()) {
            $field_html .= form_label($label, $this->_name);
        }

        if ($instructions = $this->getInstructions()) {
            $field_html .= sprintf('<p class="field-instructions">%s</p>', $instructions);
        }

        $field_html .= '</td>';
        $field_html .= '<td>';

        $field_type = $this->_type;

        $field_type_function_name = 'form_' . $field_type;

        if (!function_exists($field_type_function_name)) {
            $field_type_function_name = 'form_input';
        }

        $id_html = sprintf('id="%s"', $this->getName());

        $field_html .= $field_type_function_name($this->_name, $this->_value, $id_html);

        $field_html .= '</td>';
        $field_html .= '</tr>';
        return $field_html;
    }
}