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

class Syncee_Mcp_Site_Group_Synchronize_Channel extends Syncee_Mcp_Abstract
{
    /**
     * The GET method for viewing a synchronization_profile_id
     */
    public function synchronizeSiteGroupChannels()
    {
        $synchronization_profile_id = ee()->input->get('synchronization_profile_id');

        $synchronization_profile    = Syncee_Site_Synchronization_Profile::findByPk($synchronization_profile_id);

        if ($synchronization_profile->isEmptyRow()) {
            die('Could not find synchronization profile'); // TODO
        }

        $site_collection            = $synchronization_profile->getSiteContainer();

        $channel_comparison_library = $synchronization_profile->getComparisonCollectionLibrary();

        // sort collections by source site primary key.
        // this is to have a known and predictable way to iterate over collections and get everything in the right order.
        $channel_comparison_library->sortByCallback(function (Syncee_Entity_Comparison_Collection $a, Syncee_Entity_Comparison_Collection $b) {
            return $a->getSource()->getSite()->getPrimaryKeyValues(true) - $b->getSource()->getSite()->getPrimaryKeyValues(true);
        });

        return Syncee_View::render(__FUNCTION__, array(
            'synchronization_profile'    => $synchronization_profile,
            'site_collection'            => $site_collection,
            'entity_comparison_library'  => $channel_comparison_library,
        ), $this);
    }

    /**
     * Create a new synchronization profile and redirect to synchronizeSiteGroupChannels method with synchronization_profile_id PK appended to URL
     * TODO - this perhaps needs to be ajaxified
     */
    public function synchronizeSiteGroupChannelsPOST()
    {
        $site_group_id                   = ee()->input->get_post('site_group_id');
        $site_group                      = Syncee_Site_Group::findByPk($site_group_id);

        if ($site_group->isEmptyRow()) {
            // TODO
        }

        $synchronization_profile_factory = new Syncee_Site_Synchronization_Profile_Factory($site_group, new Syncee_Entity_Channel_Collection_Library(), new Syncee_Request_Remote_Entity_Channel());
        $synchronization_profile         = $synchronization_profile_factory->getNewSynchronizationProfile();

        $synchronization_profile->save();

        Syncee_Helper::redirect('synchronizeSiteGroupChannels', array(
            'synchronization_profile_id' => $synchronization_profile->getPrimaryKeyValues(true)
        ), $this, false);
    }

    public function synchronizeSiteGroupChannelsFixPOST()
    {
        $synchronization_profile_id = ee()->input->get_post('synchronization_profile_id');
        $synchronization_profile    = Syncee_Site_Synchronization_Profile::findByPk($synchronization_profile_id);

        if ($synchronization_profile->isEmptyRow()) {
            die('Could not find synchronization profile'); // TODO
        }

        $payload                    = json_decode(ee()->input->post('payload'), true);

        if (!is_array($payload)) {
            die('no payload'); // TODO
        }

        $synchronization_profile_decision_factory = new Syncee_Site_Synchronization_Profile_Decision_Factory($synchronization_profile, $payload);
        $synchronization_profile_decision         = $synchronization_profile_decision_factory->getNewProfileDecision();

        $synchronization_profile_decision->save();

        // Execute the merge!
        try {
            $synchronization_profile_decision->execute();
        } catch (Exception $e) {
            // TODO
            throw $e;
        }

        Syncee_Helper::redirect('synchronizeSiteGroupChannels', array(
            'synchronization_profile_id' => $synchronization_profile->getPrimaryKeyValues(true)
        ), $this, 'Site Group Channels have been merged into local site');
    }
}