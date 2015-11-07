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

class Syncee_Entity_Comparison_Fix_ChannelField_Column_FieldFormats extends Syncee_Entity_Comparison_Fix_Generic
{
    /**
     * Insert into exp_field_formatting
     * @param $comparate_value
     * @param array $decision_payload
     * @param Syncee_ActiveRecord_Abstract $active_record_row
     * @throws Syncee_Exception
     * @return self
     */
    public function performMiscTasksByDecisionPayloadAndActiveRecordRowAfterSave($comparate_value, array $decision_payload, Syncee_ActiveRecord_Abstract $active_record_row)
    {
        if (!is_array($comparate_value)) { // field_formats SHOULD be an array, throw exception if this isn't the case
            throw new Syncee_Exception('Comparate value passed to ' . __METHOD__ . ' should be an array, ' . gettype($comparate_value) . ' given');
        }

        $field_formats =& $comparate_value;

        // first purge any rows that may exist in exp_field_formatting for active record row's primary key value
        ee()->db->delete('field_formatting', array(
            'field_id' => $active_record_row->getPrimaryKeyValues(true)
        ));

        foreach ($field_formats as $field_format) {
            ee()->db->insert('field_formatting', array(
                'field_id'  => $active_record_row->getPrimaryKeyValues(true),
                'field_fmt' => $field_format['field_fmt']
            ));
        }

        return $this;
    }
}