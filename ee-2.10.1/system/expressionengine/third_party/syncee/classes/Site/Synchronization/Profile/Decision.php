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
         * @var $comparison_collection Syncee_Entity_Comparison_Collection
         */
        $synchronization_profile       = $this->getSynchronizationProfile();
        $decision_payload              = $this->getDecisionPayload();
        $site_collection               = $synchronization_profile->getSiteContainer();
        $comparison_collection_library = $synchronization_profile->getComparisonCollectionLibrary();
        $target_site                   = $comparison_collection_library->getTargetSite();
        $unique_identifier_key         = $comparison_collection_library->getUniqueIdentifierKey();

        foreach ($decision_payload as $unique_identifier_value => $row) {
            foreach ($row as $col_name => $site_id) {
                $site = $site_collection->filterByCondition(array('site_id' => $site_id), true);

                if (!$site) {
                    continue; // TODO - throw
                }

                $comparate_entity = $comparison_collection_library->getComparateEntityBySiteAndUniqueIdentifierKeyAndValue($site, $unique_identifier_key, $unique_identifier_value);

                if (!$comparate_entity) {
                    continue; // TODO - throw
                } elseif (!$comparate_entity->existsInRow($col_name)) {
                    continue; // TODO - throw
                }

                $decision_payload[$unique_identifier_value][$col_name] = $comparate_entity->$col_name;
            }
        }

        foreach ($decision_payload as $unique_identifier_value => $row) {
            $active_record_row = $comparate_entity->getActiveRecord();

            // unset primary key value(s)
            foreach ($active_record_row->getPrimaryKeyNames() as $primary_key_name) {
                unset($row[$primary_key_name]);
            }

            // find existing active record row in target by unique identifier key/value pair
            $collection = $active_record_row::findAllByCondition(array($unique_identifier_key => $unique_identifier_value));

            // if active record row exists, then overwrite so we can reassign proper primary key values from target
            if (count($collection)) {
                $active_record_row = $collection[0];
            }

            $active_record_row->assign($row);

            $active_record_row->save();
        }
    }
}