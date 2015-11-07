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


class Syncee_Site_Synchronization_Profile_Decision extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site_synchronization_profile_decision';

    /**
     * @var Syncee_Site_Synchronization_Profile
     */
    private $_synchronization_profile;

    public function setSynchronizationProfile(Syncee_Site_Synchronization_Profile $synchronization_profile)
    {
        $this->_synchronization_profile   = $synchronization_profile;
        $this->synchronization_profile_id = $synchronization_profile->getPrimaryKeyValues(true);
        return $this;
    }

    public function getSynchronizationProfile()
    {
        if (!isset($this->_synchronization_profile)) {
            $this->setSynchronizationProfile(Syncee_Site_Synchronization_Profile::findByPk($this->synchronization_profile_id));
        }

        return $this->_synchronization_profile;
    }

    public function getDecisionPayload()
    {
        return $this->decision_payload;
    }

    public function execute()
    {
        /**
         * @var $site Syncee_Site
         * @var $comparison_collection Syncee_Entity_Comparison_Collection
         */
        $synchronization_profile          = $this->getSynchronizationProfile();
        $decision_payload                 = $this->getDecisionPayload();
        $unmodified_decision_payload_copy = $decision_payload;

        $site_collection               = $synchronization_profile->getSiteContainer();
        $comparison_collection_library = $synchronization_profile->getComparisonCollectionLibrary();
        $all_comparate_column_names    = $comparison_collection_library->getAllComparateColumnNames(false);
        $target_site                   = $comparison_collection_library->getTargetSite();
        $unique_identifier_key         = $comparison_collection_library->getUniqueIdentifierKey();

        // assign values from decision payload's key/site id pairs
        foreach ($decision_payload as $unique_identifier_value => $row) {
            foreach ($all_comparate_column_names as $col_name) {
                $site_id                                 = isset($row[$col_name]) ? $row[$col_name] : null;
                $comparate_value_to_assign               = null;
                $comparate_value_is_missing_from_payload = !$site_id;

                // if comparate value is missing from payload, then determine the most appropriate value from the comparison library by evaluating frequency of site ids in decision payload
                if ($comparate_value_is_missing_from_payload) {
                    $site_ids_in_decision_payload = array_count_values($unmodified_decision_payload_copy[$unique_identifier_value]);
                    arsort($site_ids_in_decision_payload, SORT_NUMERIC);
                    $site_ids_in_decision_payload = array_keys($site_ids_in_decision_payload);

                    foreach ($site_ids_in_decision_payload as $site_id) {
                        $site = $site_collection->filterByCondition(array('site_id' => $site_id), true);

                        if (!$site) {
                            continue; // TODO - throw
                        }

                        $comparison_collection = $comparison_collection_library->getComparisonCollectionBySourceSite($site);

                        if (!$comparison_collection) {
                            continue;
                        }

                        $comparison_entity         = $comparison_collection->getComparisonEntityByComparateColumnName($col_name);
                        $comparate_value_to_assign = $comparison_entity->getSourceValue();

                        $unmodified_decision_payload_copy[$unique_identifier_value][$col_name] = $site_id;
                        break;
                    }
                } else {
                    $site = $site_collection->filterByCondition(array('site_id' => $site_id), true);

                    if (!$site) {
                        continue; // TODO - throw
                    }

                    $comparate_entity = $comparison_collection_library->getComparateEntityBySiteAndUniqueIdentifierKeyAndValue($site, $unique_identifier_key, $unique_identifier_value);

                    if (!$comparate_entity) {
                        continue; // TODO - throw
                    }

                    if (!$comparate_value_is_missing_from_payload) {
                        if (!$comparate_entity->existsInRow($col_name)) {
                            continue; // TODO - throw
                        }

                        $comparate_value_to_assign = $comparate_entity->$col_name;
                    }

                    $comparison_collection = $comparison_collection_library->getComparisonCollectionBySourceSite($site);

                    if ($comparate_value_is_missing_from_payload && !$comparison_collection) {
                        $comparison_collection = $comparison_collection_library[0];
                    }

                    $comparison_entity = $comparison_collection->getComparisonEntityByComparateColumnName($col_name);
                }

                if (isset($comparison_entity)) {
                    $comparison_entity
                        ->getFix()
                        ->modifyComparateValueByDecisionPayloadBeforeSave($comparate_value_to_assign, $decision_payload)
                        ->performMiscTasksByDecisionPayloadBeforeSave($comparate_value_to_assign, $decision_payload)
                    ;
                }

                $decision_payload[$unique_identifier_value][$col_name] = $comparate_value_to_assign;
            }
        }

        // save/update
        ee()->db->trans_begin();

        foreach ($decision_payload as $unique_identifier_value => $row) {
            $active_record_row = $comparate_entity->getActiveRecord();

            // unset primary key value(s)
            foreach ($active_record_row->getPrimaryKeyNames() as $primary_key_name) {
                unset($row[$primary_key_name]);
            }

            // find existing active record row in target by unique identifier key/value pair
            $collection = $active_record_row::findAllByCondition(array($unique_identifier_key => $unique_identifier_value));

            // if active record row exists, then overwrite so we can reassign proper primary key values from target and then implicitly perform an update when calling save method
            if (count($collection)) {
                $active_record_row = $collection[0];
            }

            $active_record_row
                ->assign($row)
                ->save()
            ;

            // call aftersave methods on comparison entities
            $unmodified_site_id_decision_payload_for_this_unique_identifier_value = $unmodified_decision_payload_copy[$unique_identifier_value];

            foreach ($row as $col_name => $value) {
                $site_id = $unmodified_site_id_decision_payload_for_this_unique_identifier_value[$col_name];

                $site = $site_collection->filterByCondition(array('site_id' => $site_id), true);

                $comparison_collection = $comparison_collection_library->getComparisonCollectionBySourceSite($site);

                $comparison_entity = $comparison_collection->getComparisonEntityByComparateColumnName($col_name);
                $comparison_entity->getFix()->performMiscTasksByDecisionPayloadAndActiveRecordRowAfterSave($value, $decision_payload, $active_record_row);
            }
        }

        ee()->db->trans_commit();
    }
}