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
    /**
     * @var bool
     */
    private $_multi;

    private $_options = array();

    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setMulti($multi)
    {
        $this->_multi = $multi;
    }

    public function getMulti()
    {
        return $this->_multi;
    }

    public function isValid()
    {
        $errors = $this->_errors = array();

        if ($this->getRequired()) {
            if ((string) $this->getValue() === '') {
                $this->_errors[] = Syncee_Field_Error::FIELD_ERROR_REQUIRED_BUT_EMPTY;
            } elseif (!array_key_exists($this->getValue(), $this->_options)) {
                $this->_errors[] = Syncee_Field_Error::FIELD_ERROR_OPTION_DOES_NOT_EXIST;
            }
        }

        return empty($errors);
    }

    public function __toString()
    {
        $field_html  = '';

        $field_html .= '<tr>';
        $field_html .= '<td>';

        if ($label = $this->getLabel()) {
            $field_html .= form_label($label, $this->_name);
        }

        $field_html .= '</td>';
        $field_html .= '<td>';

        $extra_html = sprintf('id="%s"', $this->getName());

        if ($this->getMulti()) {
            $extra_html .= ' multiple ';
        }

        $field_html .= form_dropdown($this->_name, $this->_options, $this->_value, $extra_html);

        // add disabled attribute to 1st (placeholder) option
        $field_html  = preg_replace('#<option value=""#', '<option value="" disabled ', $field_html, 1);

        $field_html .= '</td>';
        $field_html .= '</tr>';

        return $field_html;
    }
}