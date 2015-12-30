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

class Syncee_Entity_Comparison_Fix_Generic extends Syncee_Entity_Comparison_Fix_Abstract
{
    /**
     * @var Syncee_Entity_Comparison
     */
    protected $_comparison;

    public function __construct(Syncee_Entity_Comparison $comparison)
    {
        $this->_comparison = $comparison;
    }

    public function modifyComparateValueByDecisionPayloadBeforeSave(&$comparate_value, array $decision_payload)
    {
        $comparate_value_is_json_and_needs_to_be_serialized_and_base64_encoded = (
            $this->_comparison->comparateColumnIsSerializedAndBase64Encoded() &&
            ($comparate_value_json_decoded = json_decode($comparate_value, true)) &&
            is_array($comparate_value_json_decoded)
        );

        if ($comparate_value_is_json_and_needs_to_be_serialized_and_base64_encoded) {
            $comparate_value = base64_encode(serialize($comparate_value_json_decoded));
        }

        return $this;
    }

    public function performMiscTasksByDecisionPayloadBeforeSave($comparate_value, array $decision_payload)
    {
        // nothing to do in generic fixes; leave comparate value as is and perform no tasks
        return $this;
    }

    public function performMiscTasksByDecisionPayloadAndActiveRecordRowAfterSave($comparate_value, array $decision_payload, Syncee_ActiveRecord_Abstract $active_record_row)
    {
        // nothing to do in generic fixes; leave comparate value as is and perform no tasks
        return $this;
    }
}