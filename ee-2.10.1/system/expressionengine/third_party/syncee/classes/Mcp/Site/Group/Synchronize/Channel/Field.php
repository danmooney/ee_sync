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

class Syncee_Mcp_Site_Group_Synchronize_Channel_Field extends Syncee_Mcp_Abstract
{
    public function synchronizeSiteGroupChannelFields()
    {
        $synchronization_profile_id = ee()->input->get('synchronization_profile_id');

        $synchronization_profile    = Syncee_Site_Synchronization_Profile::findByPk($synchronization_profile_id);

        if ($synchronization_profile->isEmptyRow()) {
            die('Could not find synchronization profile'); // TODO
        }

        $site_collection    = $synchronization_profile->getSiteContainer();

        $comparison_library = $synchronization_profile->getComparisonCollectionLibrary();

        // sort collections by source site primary key.
        // this is to have a known and predictable way to iterate over collections and get everything in the right order.
        $comparison_library->sortByCallback(function (Syncee_Entity_Comparison_Collection $a, Syncee_Entity_Comparison_Collection $b) {
            return $a->getSource()->getSite()->getPrimaryKeyValues(true) - $b->getSource()->getSite()->getPrimaryKeyValues(true);
        });

        return Syncee_View::render('synchronizeSiteGroupChannels', array(
            'synchronization_profile'    => $synchronization_profile,
            'site_collection'            => $site_collection,
            'entity_comparison_library'  => $comparison_library,
        ), $this);
    }

    public function synchronizeSiteGroupChannelFieldsPOST()
    {
        $site_group_id                   = ee()->input->get_post('site_group_id');
        $site_group                      = Syncee_Site_Group::findByPk($site_group_id);

        if ($site_group->isEmptyRow()) {
            // TODO
        }

        $synchronization_profile_factory = new Syncee_Site_Synchronization_Profile_Factory(
            $site_group,
            new Syncee_Entity_Channel_Field_Collection_Library(),
            new Syncee_Request_Remote_Entity_Channel_Field()
        );

        $synchronization_profile         = $synchronization_profile_factory->getNewSynchronizationProfile();

        $synchronization_profile->save();

        Syncee_Helper::redirect('synchronizeSiteGroupChannelFields', array(
            'synchronization_profile_id' => $synchronization_profile->getPrimaryKeyValues(true)
        ), $this, false);
    }
}