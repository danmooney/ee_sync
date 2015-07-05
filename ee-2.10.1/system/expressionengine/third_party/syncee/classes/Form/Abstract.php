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

abstract class Syncee_Form_Abstract implements Syncee_Form_Interface
{
    protected $_action;

    protected $_fields = array();

    protected $_values = array();

    protected $_errors = array();

    protected $_button_text_by_method = array();

    /**
     * @var Syncee_Mcp_Abstract
     */
    protected $_mcp;

    public function __construct(Syncee_ActiveRecord_Abstract $row, Syncee_Mcp_Abstract $mcp)
    {
        $this->_mcp    = $mcp;

        $data          = $row->toArray(false);
        $this->_values = array_intersect_key($data, $this->_fields);

        foreach ($this->_fields as $name => $field) {
            $value = isset($this->_values[$name])
                ? $this->_values[$name]
                : null
            ;

            $type = isset($field['type'])
                ? $field['type']
                : null
            ;

            if (isset($type) && class_exists('Syncee_Field_' . ucfirst($type))) {
                $class_name = 'Syncee_Field_' . ucfirst($type);
            } else {
                $class_name = 'Syncee_Field';
            }

            $field['value']       = $value;
            $field['name']        = $name;
            $this->_fields[$name] = new $class_name($field);
        }

        $class_method_names = get_class_methods($this);

        foreach ($class_method_names as $class_method_name) {
            if (strpos($class_method_name, 'element') !== 0) {
                continue;
            }

            $field_name_camel_case = preg_replace('#^element#', '', $class_method_name);
            $field_name            = Syncee_Helper::convertCamelCaseToUnderscore($field_name_camel_case);

            $this->$class_method_name($this->_fields[$field_name]);
        }
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @param $name
     * @return Syncee_Field|bool
     */
    public function getFieldByName($name)
    {
        return isset($this->_fields[$name])
            ? $this->_fields[$name]
            : false
        ;
    }

    public function isValid()
    {
        $errors = array();

        /**
         * @var $field Syncee_Field
         */
        foreach ($this->_fields as $field) {
            if (!$field->isValid()) {

            }
        }

        $this->_errors = $errors;

        return count($errors);
    }

    public function __toString()
    {
        $form_html  = form_open();

        // override EE's over-opinionated action attribute override
        $form_html  = preg_replace('#action=".*"\s#', sprintf('action="%s" ', $this->_action), $form_html);

        /**
         * @var $field Syncee_Field
         */
        foreach ($this->_fields as $name => $field) {
            $form_html .= $field;
        }


        // add submit button

        $mcp_method      = $this->_mcp->getCalledMethod();
        $method_exploded = explode('_', Syncee_Helper::convertCamelCaseToUnderscore($mcp_method));
        $method_verb     = $method_exploded[0];
        $button_label    = isset($this->_button_text_by_method[$method_verb])
            ? $this->_button_text_by_method[$method_verb]
            : 'Save'
        ;

        $form_button = form_button('', $button_label, 'class="btn"');

        // turn button into submit button so we can apply styles to it and such
        $form_button = str_replace('name=""', '', $form_button);
        $form_button = str_replace('type="button"', 'type="submit"', $form_button);
        $form_html  .= $form_button;

        $form_html  .= form_close();

        return $form_html;
    }
}