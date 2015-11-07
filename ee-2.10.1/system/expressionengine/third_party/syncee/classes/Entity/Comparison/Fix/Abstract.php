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

abstract class Syncee_Entity_Comparison_Fix_Abstract
{
    abstract public function modifyComparateValueByDecisionPayloadBeforeSave(&$comparate_value, array $decision_payload);

    abstract public function performMiscTasksByDecisionPayloadBeforeSave($comparate_value, array $decision_payload);

    abstract public function performMiscTasksByDecisionPayloadAndActiveRecordRowAfterSave($comparate_value, array $decision_payload, Syncee_ActiveRecord_Abstract $active_record_row);
}